<?php

namespace App\Services;

use App\Models\ExamSchedule;
use App\Models\Exam;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExamScheduleService
{
    /**
     * Tạo lịch thi mới cho 1 đề thi và áp dụng cho nhiều lớp.
     */
    public function createSchedules(Exam $exam, array $data): Collection
    {
        $courseSectionIds = $data['course_section_ids'] ?? [];
        $schedules = new Collection();

        DB::transaction(function () use ($exam, $data, $courseSectionIds, &$schedules) {
            foreach ($courseSectionIds as $sectionId) {
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
        $schedule->delete();
    }

    /**
     * Tự động phân sinh viên enrolled vào ca thi.
     * Lấy SV enrolled trong course_section, assign theo capacity.
     *
     * @return int Số SV đã assign
     */
    public function autoAssignStudents(ExamSchedule $schedule): int
    {
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

        // Giới hạn theo max_students nếu có
        if ($schedule->max_students) {
            $currentCount = $schedule->scheduleStudents()->count();
            $remaining = max(0, $schedule->max_students - $currentCount);
            $toAssign = $toAssign->take($remaining);
        }

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
        return DB::transaction(function () use ($schedule, $studentIds) {
            // Xóa toàn bộ assignment cũ
            $schedule->scheduleStudents()->delete();

            // Tạo assignment mới cho từng SV
            foreach ($studentIds as $studentId) {
                $schedule->scheduleStudents()->create([
                    'student_id'        => $studentId,
                    'attendance_status' => 'pending',
                ]);
            }

            return $studentIds->count();
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

        return $query->orderBy('exam_date')->orderBy('start_time')->get();
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
        ->orderBy('exam_date', 'desc')
        ->orderBy('start_time')
        ->get();
    }
}
