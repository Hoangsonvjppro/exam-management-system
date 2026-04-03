<?php

namespace App\Services;

use App\Models\ExamSchedule;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Enums\ExamAttemptStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamScheduleService
{
    public function __construct(
        private readonly SemesterGovernanceService $semesterGovernanceService,
    ) {}

    /**
     * Tạo lịch thi mới cho 1 đề thi và áp dụng cho nhiều lớp.
     */
    public function createSchedules(Exam $exam, array $data): Collection
    {
        $courseSectionIds = $data['course_section_ids'] ?? [];
        $schedules = new Collection();

        DB::transaction(function () use ($exam, $data, $courseSectionIds, &$schedules) {
            foreach ($courseSectionIds as $sectionId) {
                $section = \App\Models\CourseSection::query()
                    ->with('semester')
                    ->findOrFail($sectionId);

                $this->semesterGovernanceService->assertSectionAllowsExamScheduling($section);

                $scheduleData = $data;
                unset($scheduleData['course_section_ids']);
                $scheduleData['course_section_id'] = $sectionId;

                $schedule = $exam->schedules()->create($scheduleData);

                // Tự động phân sinh viên sau khi tạo
                $this->autoAssignStudents($schedule);

                // Tự động tạo cột điểm nếu yêu cầu
                if (!empty($data['link_grade_column'])) {
                    $maxOrder = $schedule->courseSection->gradeColumns()->max('order') ?? 0;
                    $schedule->courseSection->gradeColumns()->create([
                        'name' => 'Điểm bài thi: ' . $exam->title,
                        'weight' => 0, // GV sẽ tự chỉnh trọng số sau
                        'is_exam_linked' => true,
                        'exam_schedule_id' => $schedule->id,
                        'order' => $maxOrder + 1,
                    ]);
                }

                $schedules->push($schedule);
            }
        });

        return $schedules;
    }

    /**
     * Tạo lịch thi mới cho 1 đề thi.
     */
    public function createSchedule(Exam $exam, array $data): ExamSchedule
    {
        return $exam->schedules()->create($data);
    }

    /**
     * Cập nhật lịch thi.
     */
    public function updateSchedule(ExamSchedule $schedule, array $data): ExamSchedule
    {
        $schedule->loadMissing('courseSection.semester', 'exam');
        $this->semesterGovernanceService->assertScheduleCanMutate($schedule);

        $schedule->update($data);

        if (!empty($data['link_grade_column'])) {
            // Check if already linked
            if (!$schedule->courseSection->gradeColumns()->where('exam_schedule_id', $schedule->id)->exists()) {
                $maxOrder = $schedule->courseSection->gradeColumns()->max('order') ?? 0;
                $schedule->courseSection->gradeColumns()->create([
                    'name' => 'Điểm bài thi: ' . $schedule->exam->title,
                    'weight' => 0,
                    'is_exam_linked' => true,
                    'exam_schedule_id' => $schedule->id,
                    'order' => $maxOrder + 1,
                ]);
            }
        } elseif (array_key_exists('grade_column_id', $data)) {
            // Unlink current
            $schedule->courseSection->gradeColumns()->where('exam_schedule_id', $schedule->id)->update([
                'exam_schedule_id' => null,
                'is_exam_linked' => false
            ]);

            // Link new
            if ($data['grade_column_id']) {
                $schedule->courseSection->gradeColumns()->where('id', $data['grade_column_id'])->update([
                    'exam_schedule_id' => $schedule->id,
                    'is_exam_linked' => true
                ]);
            }
        }

        // Tự động đồng bộ lại điểm cho các bài thi đã nộp trước đó
        $gradeCol = $schedule->courseSection->gradeColumns()->where('exam_schedule_id', $schedule->id)->first();
        if ($gradeCol) {
            $completedAttempts = $schedule->attempts()->where('status', \App\Enums\ExamAttemptStatus::Completed)->get();
            foreach ($completedAttempts as $attempt) {
                \App\Models\StudentGrade::updateOrCreate(
                    [
                        'grade_column_id' => $gradeCol->id,
                        'student_id'      => $attempt->user_id,
                    ],
                    [
                        'score' => $attempt->total_score,
                        'note'  => 'Đồng bộ tự động (Retroactive)',
                    ]
                );
            }
        }

        return $schedule;
    }

    public function deleteSchedule(ExamSchedule $schedule): void
    {
        $schedule->loadMissing('courseSection.semester');
        $this->semesterGovernanceService->assertScheduleCanMutate($schedule);

        if (! $schedule->can_edit) {
            throw new \DomainException('Không thể xóa ca thi đã bắt đầu hoặc đã kết thúc.');
        }

        $schedule->delete();
    }

    public function cancelSchedule(ExamSchedule $schedule): ExamSchedule
    {
        $schedule->loadMissing('courseSection.semester');
        $this->semesterGovernanceService->assertScheduleCanMutate($schedule);

        return DB::transaction(function () use ($schedule) {
            $schedule->update(['status' => 'cancelled']);

            return $schedule->fresh();
        });
    }

    /**
     * Tự động phân sinh viên enrolled vào ca thi.
     * Lấy SV enrolled trong course_section, assign theo capacity.
     *
     * @return int Số SV đã assign
     */
    public function autoAssignStudents(ExamSchedule $schedule): int
    {
        $schedule->loadMissing('courseSection.semester');
        $this->semesterGovernanceService->assertScheduleCanMutate($schedule);

        $courseSection = $schedule->courseSection;

        if (! $courseSection) {
            return 0;
        }

        // Lấy danh sách SV enrolled chưa được assign vào ca thi này
        $enrolledStudentIds = $courseSection->students()
            ->wherePivot('status', 'enrolled')
            ->pluck('users.id');

        $alreadyAssignedIds = $schedule->scheduleStudents()
            ->pluck('student_id');

        $toAssign = $enrolledStudentIds->diff($alreadyAssignedIds);

        DB::transaction(function () use ($schedule, $toAssign) {
            foreach ($toAssign as $studentId) {
                $schedule->scheduleStudents()->create([
                    'student_id' => $studentId,
                    'attendance_status' => 'pending',
                ]);
            }
        });

        return $toAssign->count();
    }

    /**
     * Đồng bộ danh sách SV được phân vào ca thi.
     * Xóa toàn bộ assignment cũ, tạo lại theo danh sách studentIds mới.
     *
     * @param \Illuminate\Support\Collection<int, int> $studentIds
     * @return int Số SV đã assign
     */
    public function syncAssignedStudents(ExamSchedule $schedule, \Illuminate\Support\Collection $studentIds): int
    {
        $schedule->loadMissing('courseSection.semester');
        $this->semesterGovernanceService->assertScheduleCanMutate($schedule);

        if (! $schedule->can_edit) {
            throw ValidationException::withMessages([
                'schedule' => 'Không thể thay đổi danh sách sinh viên khi ca thi đã bắt đầu hoặc đã kết thúc.',
            ]);
        }

        $courseSection = $schedule->courseSection;

        if (! $courseSection) {
            throw ValidationException::withMessages([
                'schedule' => 'Ca thi chưa gắn với lớp học phần hợp lệ.',
            ]);
        }

        $normalizedStudentIds = $studentIds
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $allowedStudentIds = $courseSection->students()
            ->wherePivot('status', 'enrolled')
            ->pluck('users.id')
            ->map(fn($id) => (int) $id)
            ->values();

        $invalidStudentIds = $normalizedStudentIds->diff($allowedStudentIds);
        if ($invalidStudentIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'student_ids' => 'Danh sách sinh viên chứa tài khoản không thuộc lớp hoặc không còn trạng thái đang học.',
            ]);
        }

        if ($schedule->max_students !== null && $normalizedStudentIds->count() > (int) $schedule->max_students) {
            throw ValidationException::withMessages([
                'student_ids' => 'Số lượng sinh viên vượt quá sức chứa của ca thi.',
            ]);
        }

        return DB::transaction(function () use ($schedule, $normalizedStudentIds) {
            // Xóa toàn bộ assignment cũ
            $schedule->scheduleStudents()->delete();

            // Tạo assignment mới cho từng SV
            foreach ($normalizedStudentIds as $studentId) {
                $schedule->scheduleStudents()->create([
                    'student_id'        => $studentId,
                    'attendance_status' => 'pending',
                ]);
            }

            return $normalizedStudentIds->count();
        });
    }

    /**
     * Lấy danh sách SV trong lớp và trạng thái đã phân vào ca thi.
     */
    public function getStudentsForAssignment(ExamSchedule $schedule): array
    {
        $courseSection = $schedule->courseSection;
        if (!$courseSection) {
            return ['students' => collect(), 'assigned_ids' => []];
        }

        // Lấy SV enrolled trong lớp
        $students = $courseSection->students()
            ->wherePivot('status', 'enrolled')
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email', 'users.student_code']);

        // Lấy danh sách SV đã được assign
        $assignedIds = $schedule->scheduleStudents()->pluck('student_id')->toArray();

        return [
            'students' => $students,
            'assigned_ids' => $assignedIds
        ];
    }

    /**
     * Lấy danh sách lịch thi của giảng viên.
     */
    public function getSchedulesForLecturer(int $lecturerId, ?int $semesterId = null, ?string $search = null, ?string $subjectId = null): Collection
    {
        $query = ExamSchedule::whereHas('courseSection', function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId);
        })->with(['exam.subject', 'courseSection']);

        if ($semesterId) {
            $query->whereHas('courseSection', fn($q) => $q->where('semester_id', $semesterId));
        }

        if ($search) {
            $query->whereHas('exam', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        }

        if ($subjectId) {
            $query->whereHas('exam', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        return $query->orderByRaw('TIMESTAMP(exam_date, start_time)')->get();
    }

    /**
     * Lấy danh sách lịch thi của sinh viên (dựa trên lớp đã enrolled).
     */
    public function getSchedulesForStudent(int $studentId): Collection
    {
        return ExamSchedule::whereHas('courseSection.students', function ($q) use ($studentId) {
            $q->where('users.id', $studentId)
                ->where('course_section_students.status', 'enrolled');
        })
            ->with(['exam.subject', 'courseSection'])
            ->orderByRaw('TIMESTAMP(exam_date, start_time) DESC')
            ->get();
    }

    /**
     * Dữ liệu giám sát ca thi cho giảng viên.
     *
     * @return array<string, mixed>
     */
    public function getMonitoringData(ExamSchedule $schedule): array
    {
        $assignedStudents = $schedule->students()
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.email', 'users.student_code']);

        $latestAttemptsByUser = ExamAttempt::query()
            ->where('exam_schedule_id', $schedule->id)
            ->whereIn('user_id', $assignedStudents->pluck('id'))
            ->orderByDesc('attempt_number')
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $notStarted = collect();
        $inProgress = collect();
        $submitted = collect();

        foreach ($assignedStudents as $student) {
            $attempt = $latestAttemptsByUser->get($student->id);

            $studentBase = [
                'student_id' => (int) $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'student_code' => $student->student_code,
            ];

            if (! $attempt) {
                $notStarted->push($studentBase);
                continue;
            }

            if ($attempt->status === ExamAttemptStatus::InProgress) {
                $inProgress->push(array_merge($studentBase, [
                    'attempt_id' => (int) $attempt->id,
                    'attempt_number' => (int) $attempt->attempt_number,
                    'started_at' => $attempt->started_at,
                    'submitted_answers_count' => (int) ($attempt->submitted_answers_count ?? 0),
                    'tab_switch_count' => (int) ($attempt->tab_switch_count ?? 0),
                ]));
                continue;
            }

            if ($attempt->status === ExamAttemptStatus::Completed) {
                $submitted->push(array_merge($studentBase, [
                    'attempt_id' => (int) $attempt->id,
                    'attempt_number' => (int) $attempt->attempt_number,
                    'started_at' => $attempt->started_at,
                    'completed_at' => $attempt->completed_at,
                    'total_score' => $attempt->total_score,
                    'submitted_answers_count' => (int) ($attempt->submitted_answers_count ?? 0),
                    'tab_switch_count' => (int) ($attempt->tab_switch_count ?? 0),
                ]));
                continue;
            }

            // Fallback cho trạng thái hiếm gặp (vd: abandoned).
            $notStarted->push($studentBase);
        }

        $warnings = $inProgress
            ->map(fn(array $row) => array_merge($row, ['attempt_status' => 'in_progress']))
            ->merge($submitted->map(fn(array $row) => array_merge($row, ['attempt_status' => 'completed'])))
            ->filter(fn(array $row) => $row['tab_switch_count'] > 0)
            ->sortByDesc('tab_switch_count')
            ->values()
            ->map(function (array $row): array {
                $tabSwitchCount = $row['tab_switch_count'];

                if ($tabSwitchCount >= 3) {
                    $row['warning_level'] = 'high';
                    $row['warning_message'] = 'Chuyển tab từ 3 lần trở lên';
                    return $row;
                }

                if ($tabSwitchCount === 2) {
                    $row['warning_level'] = 'medium';
                    $row['warning_message'] = 'Chuyển tab 2 lần';
                    return $row;
                }

                $row['warning_level'] = 'low';
                $row['warning_message'] = 'Chuyển tab 1 lần';
                return $row;
            });

        $assignedCount = $assignedStudents->count();
        $submittedCount = $submitted->count();

        return [
            'assignedCount' => $assignedCount,
            'notStartedCount' => $notStarted->count(),
            'inProgressCount' => $inProgress->count(),
            'submittedCount' => $submittedCount,
            'completionRate' => $assignedCount > 0
                ? round(($submittedCount / $assignedCount) * 100, 1)
                : 0,
            'notStartedStudents' => $notStarted->values(),
            'inProgressStudents' => $inProgress->values(),
            'submittedStudents' => $submitted->values(),
            'warnings' => $warnings,
        ];
    }
}
