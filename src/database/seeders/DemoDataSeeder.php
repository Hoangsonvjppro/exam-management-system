<?php

namespace Database\Seeders;

use App\Enums\ExamAttemptStatus;
use App\Models\Admin;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Complaint;
use App\Models\CourseSection;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamSchedule;
use App\Models\GradeColumn;
use App\Models\LeaveRequest;
use App\Models\StudentAnswer;
use App\Models\StudentAnswerOption;
use App\Models\StudentGrade;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\AttendanceGradeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * ============================================================
 * DemoDataSeeder — Dữ liệu mô phỏng end-to-end cho demo
 * ============================================================
 * Bổ sung sau seed nền:
 * - Announcement + user notifications
 * - Attendance sessions + attendance records + leave requests
 * - Cột điểm quá trình + điểm thi đồng bộ
 * - Kịch bản lịch thi theo timeline (completed/upcoming/in-range)
 * - Exam attempts + answers + complaint lifecycle
 * ============================================================
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        /** @var EloquentCollection<int, CourseSection> $sections */
        $sections = CourseSection::query()
            ->with(['subject', 'lecturer'])
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        if ($sections->isEmpty()) {
            $this->command->warn('⚠ Không có lớp học phần active. Bỏ qua DemoDataSeeder.');
            return;
        }

        $attendanceGradeService = app(AttendanceGradeService::class);

        $announcementCount = $this->seedAnnouncements();
        $notificationTouchCount = 0;
        $attendanceSessionCount = 0;
        $leaveRequestCount = 0;

        foreach ($sections as $section) {
            /** @var CourseSection $section */
            $students = $this->getEnrolledStudents($section);
            if ($students->isEmpty()) {
                continue;
            }

            $notificationTouchCount += $this->seedSectionNotifications($section, $students);

            [$sessionTouched, $leaveTouched] = $this->seedAttendanceAndLeaveData(
                $section,
                $students,
                $attendanceGradeService,
            );

            $attendanceSessionCount += $sessionTouched;
            $leaveRequestCount += $leaveTouched;
        }

        $publishedSchedules = ExamSchedule::query()
            ->with(['exam.examQuestions', 'courseSection.lecturer'])
            ->whereHas('exam', fn($query) => $query->where('status', 'published'))
            ->orderBy('id')
            ->get();

        $attemptCount = 0;
        $complaintCount = 0;

        if ($publishedSchedules->isNotEmpty()) {
            $completedSchedule = $this->prepareScheduleTimeline($publishedSchedules);

            if ($completedSchedule) {
                $attemptCount = $this->seedExamAttemptsAndGrades($completedSchedule);
                $complaintCount = $this->seedComplaintsForCompletedSchedule($completedSchedule);
            }
        }

        $this->command->info('✅ DemoDataSeeder hoàn tất:');
        $this->command->line("   - Announcement: {$announcementCount}");
        $this->command->line("   - Notification (touch): {$notificationTouchCount}");
        $this->command->line("   - Buổi điểm danh (touch): {$attendanceSessionCount}");
        $this->command->line("   - Đơn xin phép (touch): {$leaveRequestCount}");
        $this->command->line("   - Attempt hoàn thành: {$attemptCount}");
        $this->command->line("   - Khiếu nại (touch): {$complaintCount}");
    }

    private function seedAnnouncements(): int
    {
        $adminId = Admin::query()->value('id');

        $rows = [
            [
                'title' => 'Khởi động giai đoạn ôn tập giữa kỳ',
                'body' => 'Các lớp học phần bắt đầu tăng cường ôn tập. Sinh viên chủ động theo dõi lịch thi và hoàn thành bài tập đúng hạn.',
                'type' => 'info',
                'is_published' => true,
            ],
            [
                'title' => 'Nhắc nhở chuyên cần và đơn xin phép',
                'body' => 'Sinh viên vắng học cần nộp đơn xin phép có minh chứng trong vòng 24h để được xét chuyên cần.',
                'type' => 'warning',
                'is_published' => true,
            ],
            [
                'title' => 'Mở phòng máy tự học cuối tuần',
                'body' => 'Phòng LAB01 và LAB02 mở cửa 08:00 - 17:00 Thứ bảy cho sinh viên luyện tập trước kỳ thi.',
                'type' => 'event',
                'is_published' => true,
            ],
            [
                'title' => 'Thông báo bảo trì hệ thống thi trực tuyến',
                'body' => 'Hệ thống sẽ bảo trì ngắn vào 23:00 Chủ nhật. Trong thời gian này không thể bắt đầu bài thi mới.',
                'type' => 'urgent',
                'is_published' => true,
            ],
        ];

        foreach ($rows as $row) {
            Announcement::updateOrCreate(
                ['title' => $row['title']],
                array_merge($row, ['created_by' => $adminId])
            );
        }

        return count($rows);
    }

    private function getEnrolledStudents(CourseSection $section): Collection
    {
        return $section->students()
            ->wherePivot('status', 'enrolled')
            ->orderBy('users.id')
            ->get();
    }

    private function seedSectionNotifications(CourseSection $section, Collection $students): int
    {
        $subjectCode = $section->subject?->code ?? 'N/A';

        $templates = [
            [
                'type' => 'course_announcement',
                'title' => "[{$section->code}] Nhắc lịch học tuần này",
                'message' => "Lớp {$section->code} ({$subjectCode}) vẫn học đúng lịch trong tuần. Sinh viên chuẩn bị bài trước khi đến lớp.",
                'read_at' => now()->subDays(2),
            ],
            [
                'type' => 'grade_update',
                'title' => "[{$section->code}] Cập nhật điểm quá trình",
                'message' => 'Giảng viên đã cập nhật thêm điểm quá trình. Vui lòng vào tab Điểm để kiểm tra chi tiết.',
                'read_at' => null,
            ],
            [
                'type' => 'attendance_alert',
                'title' => "[{$section->code}] Cảnh báo chuyên cần",
                'message' => 'Hệ thống đã ghi nhận tình trạng điểm danh mới nhất. Nếu có sai sót, hãy gửi phản hồi cho giảng viên.',
                'read_at' => null,
            ],
        ];

        $touchCount = 0;

        foreach ($students as $student) {
            foreach ($templates as $template) {
                UserNotification::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'type' => $template['type'],
                        'title' => $template['title'],
                    ],
                    [
                        'message' => $template['message'],
                        'data' => [
                            'course_section_id' => $section->id,
                            'course_section_code' => $section->code,
                        ],
                        'read_at' => $template['read_at'],
                    ]
                );

                $touchCount++;
            }
        }

        return $touchCount;
    }

    /**
     * @return array{int, int}
     */
    private function seedAttendanceAndLeaveData(
        CourseSection $section,
        Collection $students,
        AttendanceGradeService $attendanceGradeService,
    ): array {
        $baseDate = now()->copy()->startOfDay()->subWeeks(5);

        $sessionBlueprint = [
            ['title' => 'Buổi 01 - Khởi động', 'offset_days' => 0],
            ['title' => 'Buổi 02 - Luyện tập', 'offset_days' => 7],
            ['title' => 'Buổi 03 - Chữa bài', 'offset_days' => 14],
            ['title' => 'Buổi 04 - Tổng hợp', 'offset_days' => 21],
            ['title' => 'Buổi 05 - Ôn tập', 'offset_days' => 28],
        ];

        $sessions = collect();

        foreach ($sessionBlueprint as $idx => $item) {
            $sessionDate = $baseDate
                ->copy()
                ->addDays($item['offset_days'])
                ->setTime(7 + ($idx % 3), 30, 0);

            $secretCode = strtoupper(substr(md5($section->code . '-' . $item['title']), 0, 6));

            $session = AttendanceSession::updateOrCreate(
                [
                    'course_section_id' => $section->id,
                    'title' => $item['title'],
                ],
                [
                    'date' => $sessionDate,
                    'secret_code' => $secretCode,
                    'is_open' => false,
                    'penalty_applied_at' => $sessionDate->copy()->addHours(3),
                ]
            );

            $sessions->push($session);
        }

        $attendanceColumn = $attendanceGradeService->ensureColumn($section);

        $studentStats = [];

        foreach ($students->values() as $studentIndex => $student) {
            foreach ($sessions->values() as $sessionIndex => $session) {
                $status = $this->resolveAttendanceStatus(
                    sectionId: (int) $section->id,
                    studentIndex: $studentIndex,
                    sessionIndex: $sessionIndex,
                );

                $note = match ($status) {
                    'absent' => 'Vắng mặt không phép (demo)',
                    'excused' => 'Vắng có phép (demo)',
                    default => null,
                };

                AttendanceRecord::updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => $status,
                        'note' => $note,
                    ]
                );

                $studentStats[$student->id]['absent'] = ($studentStats[$student->id]['absent'] ?? 0)
                    + ($status === 'absent' ? 1 : 0);
                $studentStats[$student->id]['excused'] = ($studentStats[$student->id]['excused'] ?? 0)
                    + ($status === 'excused' ? 1 : 0);
            }
        }

        foreach ($students as $student) {
            $absent = (int) ($studentStats[$student->id]['absent'] ?? 0);
            $excused = (int) ($studentStats[$student->id]['excused'] ?? 0);

            $score = AttendanceGradeService::DEFAULT_SCORE
                - ($absent * AttendanceGradeService::ABSENT_PENALTY)
                - ($excused * AttendanceGradeService::APPROVED_LEAVE_PENALTY);

            $score = max(0, round($score, 2));

            StudentGrade::updateOrCreate(
                [
                    'grade_column_id' => $attendanceColumn->id,
                    'student_id' => $student->id,
                ],
                [
                    'score' => $score,
                    'note' => 'Cập nhật tự động từ dữ liệu điểm danh demo',
                    'updated_by' => $section->lecturer_id,
                ]
            );
        }

        $leaveRequestCount = $this->seedLeaveRequests($section, $students, $sessions);

        return [$sessions->count(), $leaveRequestCount];
    }

    private function resolveAttendanceStatus(int $sectionId, int $studentIndex, int $sessionIndex): string
    {
        $seed = ($sectionId * 13) + (($studentIndex + 1) * 17) + (($sessionIndex + 1) * 19);

        if ($seed % 11 === 0) {
            return 'excused';
        }

        if ($seed % 5 === 0) {
            return 'absent';
        }

        return 'present';
    }

    private function seedLeaveRequests(CourseSection $section, Collection $students, Collection $sessions): int
    {
        if ($students->count() < 3 || $sessions->count() < 3) {
            return 0;
        }

        $rows = [
            [
                'student' => $students->values()->get(0),
                'session' => $sessions->values()->get(1),
                'status' => 'approved',
                'reason' => 'Xin phép nghỉ do tham gia hoạt động nghiên cứu khoa học cấp khoa.',
                'proof' => 'leave-proofs/demo-approved-01.jpg',
            ],
            [
                'student' => $students->values()->get(1),
                'session' => $sessions->values()->get(2),
                'status' => 'pending',
                'reason' => 'Đăng ký nghỉ để đi khám bệnh theo lịch hẹn.',
                'proof' => 'leave-proofs/demo-pending-01.jpg',
            ],
            [
                'student' => $students->values()->get(2),
                'session' => $sessions->values()->get(3),
                'status' => 'rejected',
                'reason' => 'Nghỉ học do bận việc cá nhân nhưng chưa kèm minh chứng hợp lệ.',
                'proof' => null,
            ],
        ];

        $count = 0;

        foreach ($rows as $row) {
            /** @var User|null $student */
            $student = $row['student'];
            /** @var AttendanceSession|null $session */
            $session = $row['session'];

            if (! $student || ! $session) {
                continue;
            }

            LeaveRequest::updateOrCreate(
                [
                    'course_section_id' => $section->id,
                    'student_id' => $student->id,
                    'date' => Carbon::parse($session->date)->toDateString(),
                ],
                [
                    'reason' => $row['reason'],
                    'proof_image_path' => $row['proof'],
                    'status' => $row['status'],
                ]
            );

            $count++;
        }

        return $count;
    }

    private function prepareScheduleTimeline(Collection $schedules): ?ExamSchedule
    {
        /** @var ExamSchedule|null $completedSchedule */
        $completedSchedule = $schedules->first();

        if (! $completedSchedule) {
            return null;
        }

        $completedDate = now()->copy()->subDays(4)->toDateString();

        $completedSchedule->update([
            'exam_date' => $completedDate,
            'end_date' => $completedDate,
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'schedule_mode' => ExamSchedule::MODE_WITHIN_DAY,
            'disable_attempt_timer' => false,
            'status' => 'completed',
            'notes' => 'Dữ liệu demo: ca thi đã hoàn tất và có kết quả.',
        ]);

        $this->syncScheduleStudents($completedSchedule);

        /** @var ExamSchedule|null $upcomingSchedule */
        $upcomingSchedule = $schedules->skip(1)->first();
        if ($upcomingSchedule) {
            $upcomingDate = now()->copy()->addDays(3)->toDateString();

            $upcomingSchedule->update([
                'exam_date' => $upcomingDate,
                'end_date' => $upcomingDate,
                'start_time' => '14:00:00',
                'end_time' => '14:45:00',
                'schedule_mode' => ExamSchedule::MODE_WITHIN_DAY,
                'disable_attempt_timer' => false,
                'status' => 'scheduled',
                'notes' => 'Dữ liệu demo: ca thi sắp diễn ra.',
            ]);

            $this->syncScheduleStudents($upcomingSchedule);
        }

        /** @var ExamSchedule|null $rangeSchedule */
        $rangeSchedule = $schedules->skip(2)->first();
        if ($rangeSchedule) {
            $rangeSchedule->update([
                'exam_date' => now()->copy()->subDay()->toDateString(),
                'end_date' => now()->copy()->addDay()->toDateString(),
                'start_time' => '00:00:00',
                'end_time' => '23:59:00',
                'schedule_mode' => ExamSchedule::MODE_IN_RANGE,
                'disable_attempt_timer' => true,
                'status' => 'scheduled',
                'notes' => 'Dữ liệu demo: cửa sổ thi linh hoạt trong khoảng ngày.',
            ]);

            $this->syncScheduleStudents($rangeSchedule);
        }

        return $completedSchedule->fresh(['exam.examQuestions', 'courseSection.lecturer']);
    }

    private function syncScheduleStudents(ExamSchedule $schedule): void
    {
        $courseSection = $schedule->courseSection;
        if (! $courseSection) {
            return;
        }

        $enrolledStudentIds = $courseSection->students()
            ->wherePivot('status', 'enrolled')
            ->pluck('users.id')
            ->map(fn($id) => (int) $id)
            ->values();

        if ($enrolledStudentIds->isEmpty()) {
            $schedule->scheduleStudents()->delete();
            return;
        }

        $schedule->scheduleStudents()
            ->whereNotIn('student_id', $enrolledStudentIds->all())
            ->delete();

        foreach ($enrolledStudentIds as $studentId) {
            $schedule->scheduleStudents()->updateOrCreate(
                ['student_id' => $studentId],
                ['attendance_status' => 'pending']
            );
        }
    }

    private function seedExamAttemptsAndGrades(ExamSchedule $schedule): int
    {
        $schedule->loadMissing(['exam.examQuestions', 'courseSection.lecturer']);

        /** @var Collection<int, ExamQuestion> $examQuestions */
        $examQuestions = $schedule->exam->examQuestions()
            ->orderBy('order_index')
            ->get();

        if ($examQuestions->isEmpty()) {
            return 0;
        }

        $students = $schedule->courseSection
            ? $schedule->courseSection->students()
            ->wherePivot('status', 'enrolled')
            ->orderBy('users.id')
            ->limit(6)
            ->get()
            : collect();

        if ($students->isEmpty()) {
            return 0;
        }

        $gradeColumn = GradeColumn::updateOrCreate(
            [
                'course_section_id' => $schedule->course_section_id,
                'exam_schedule_id' => $schedule->id,
            ],
            [
                'name' => 'Điểm bài thi: ' . $schedule->exam->title,
                'weight' => 40,
                'is_exam_linked' => true,
                'order' => 90,
            ]
        );

        $accuracyProfiles = [0.92, 0.84, 0.76, 0.67, 0.58, 0.46];
        $attemptedStudentIds = [];

        $examDate = Carbon::parse((string) $schedule->exam_date)->toDateString();
        $endDate = Carbon::parse((string) ($schedule->end_date ?: $schedule->exam_date))->toDateString();

        $scheduleStart = Carbon::parse($examDate . ' ' . $schedule->start_time);
        $scheduleEnd = Carbon::parse($endDate . ' ' . $schedule->end_time);

        foreach ($students->values() as $index => $student) {
            $startedAt = $scheduleStart->copy()->addMinutes($index * 3);
            if ($startedAt->gte($scheduleEnd)) {
                $startedAt = $scheduleStart->copy();
            }

            $completedAt = $startedAt->copy()->addMinutes(25 + ($index % 3) * 5);
            if ($completedAt->gte($scheduleEnd)) {
                $completedAt = $scheduleEnd->copy()->subMinute();
            }

            if ($completedAt->lte($startedAt)) {
                $completedAt = $startedAt->copy()->addMinutes(5);
            }

            $focusLostAt = $index % 3 === 0
                ? [$startedAt->copy()->addMinutes(8)->toIso8601String()]
                : [];

            $attempt = ExamAttempt::updateOrCreate(
                [
                    'exam_schedule_id' => $schedule->id,
                    'user_id' => $student->id,
                    'attempt_number' => 1,
                ],
                [
                    'started_at' => $startedAt,
                    'completed_at' => $completedAt,
                    'status' => ExamAttemptStatus::Completed,
                    'ip_address' => '127.0.0.' . (($index % 9) + 1),
                    'user_agent' => 'DemoSeeder/1.0',
                    'focus_lost_at' => $focusLostAt,
                    'tab_switch_count' => count($focusLostAt),
                    'current_question_id' => $examQuestions->last()?->question_id,
                ]
            );

            [$totalScore, $correctCount, $submittedAnswersCount] = $this->seedAnswersForAttempt(
                attempt: $attempt,
                examQuestions: $examQuestions,
                accuracy: $accuracyProfiles[$index % count($accuracyProfiles)],
            );

            $attempt->update([
                'status' => ExamAttemptStatus::Completed,
                'completed_at' => $completedAt,
                'total_score' => $totalScore,
                'correct_count' => $correctCount,
                'submitted_answers_count' => $submittedAnswersCount,
            ]);

            StudentGrade::updateOrCreate(
                [
                    'grade_column_id' => $gradeColumn->id,
                    'student_id' => $student->id,
                ],
                [
                    'score' => $totalScore,
                    'note' => 'Đồng bộ tự động từ dữ liệu bài thi demo',
                    'updated_by' => $schedule->courseSection?->lecturer_id,
                ]
            );

            $attemptedStudentIds[] = (int) $student->id;
        }

        if (! empty($attemptedStudentIds)) {
            $schedule->scheduleStudents()
                ->whereIn('student_id', $attemptedStudentIds)
                ->update(['attendance_status' => 'present']);

            $schedule->scheduleStudents()
                ->whereNotIn('student_id', $attemptedStudentIds)
                ->update(['attendance_status' => 'absent']);
        }

        return count($attemptedStudentIds);
    }

    /**
     * @return array{float, int, int}
     */
    private function seedAnswersForAttempt(ExamAttempt $attempt, Collection $examQuestions, float $accuracy): array
    {
        $correctUnits = 0.0;
        $correctCount = 0;
        $submittedAnswers = 0;

        foreach ($examQuestions->values() as $index => $examQuestion) {
            $snapshot = is_array($examQuestion->question_snapshot)
                ? $examQuestion->question_snapshot
                : [];

            $options = collect($snapshot['options'] ?? []);
            if ($options->isEmpty()) {
                continue;
            }

            $correctOptionIds = $options
                ->where('is_correct', true)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values();

            if ($correctOptionIds->isEmpty()) {
                continue;
            }

            $questionTypeCode = (string) ($snapshot['question_type_code'] ?? 'single_choice');
            $isMultipleChoice = $questionTypeCode === 'multiple_choice' || $correctOptionIds->count() > 1;

            $answerCorrectly = $this->shouldAnswerCorrectly(
                userId: (int) $attempt->user_id,
                questionOffset: $index,
                accuracy: $accuracy,
            );

            $selectedOptionIds = [];
            $questionOptionId = null;

            if ($isMultipleChoice) {
                if ($answerCorrectly) {
                    $selectedOptionIds = $correctOptionIds->all();
                } else {
                    $selectedOptionIds = [(int) $correctOptionIds->first()];
                    $wrongOptionId = (int) ($options->firstWhere('is_correct', false)['id'] ?? 0);
                    if ($wrongOptionId > 0) {
                        $selectedOptionIds[] = $wrongOptionId;
                    }
                    $selectedOptionIds = array_values(array_unique($selectedOptionIds));
                }
            } else {
                $wrongOptionId = (int) ($options->firstWhere('is_correct', false)['id'] ?? $correctOptionIds->first());
                $questionOptionId = $answerCorrectly ? (int) $correctOptionIds->first() : $wrongOptionId;
                $selectedOptionIds = [$questionOptionId];
            }

            $isCorrect = count($selectedOptionIds) === $correctOptionIds->count()
                && empty(array_diff($selectedOptionIds, $correctOptionIds->all()))
                && empty(array_diff($correctOptionIds->all(), $selectedOptionIds));

            $pointsAwarded = $isCorrect ? 1.0 : 0.0;

            $answer = StudentAnswer::updateOrCreate(
                [
                    'exam_attempt_id' => $attempt->id,
                    'question_id' => $examQuestion->question_id,
                ],
                [
                    'question_option_id' => $questionOptionId,
                    'answer_text' => null,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $pointsAwarded,
                ]
            );

            if ($isMultipleChoice) {
                $answer->selectedOptions()
                    ->whereNotIn('question_option_id', $selectedOptionIds)
                    ->delete();

                foreach ($selectedOptionIds as $optionId) {
                    if ((int) $optionId <= 0) {
                        continue;
                    }

                    StudentAnswerOption::updateOrCreate([
                        'student_answer_id' => $answer->id,
                        'question_option_id' => (int) $optionId,
                    ]);
                }
            } else {
                $answer->selectedOptions()->delete();
            }

            $submittedAnswers++;
            $correctUnits += $pointsAwarded;
            if ($isCorrect) {
                $correctCount++;
            }
        }

        $totalQuestions = max(1, $examQuestions->count());
        $totalScore = round(($correctUnits / $totalQuestions) * 10, 1);

        return [$totalScore, $correctCount, $submittedAnswers];
    }

    private function shouldAnswerCorrectly(int $userId, int $questionOffset, float $accuracy): bool
    {
        $threshold = (int) round($accuracy * 100);
        $roll = (($userId * 31) + (($questionOffset + 1) * 17) + 13) % 100;

        return $roll < $threshold;
    }

    private function seedComplaintsForCompletedSchedule(ExamSchedule $schedule): int
    {
        $schedule->loadMissing(['courseSection.lecturer', 'exam.examQuestions']);

        $attempts = ExamAttempt::query()
            ->where('exam_schedule_id', $schedule->id)
            ->where('status', ExamAttemptStatus::Completed)
            ->orderBy('total_score')
            ->get();

        if ($attempts->isEmpty()) {
            return 0;
        }

        $totalQuestions = max(1, $schedule->exam->examQuestions->count());
        $lecturer = $schedule->courseSection?->lecturer;
        $count = 0;

        $pendingAttempt = $attempts->first();
        if ($pendingAttempt) {
            Complaint::updateOrCreate(
                [
                    'student_id' => $pendingAttempt->user_id,
                    'exam_attempt_id' => $pendingAttempt->id,
                ],
                [
                    'exam_schedule_id' => $schedule->id,
                    'course_section_id' => $schedule->course_section_id,
                    'reason' => 'Em nghi ngờ hệ thống chưa ghi nhận đầy đủ đáp án ở phần trắc nghiệm.',
                    'current_score' => (float) $pendingAttempt->total_score,
                    'status' => 'pending',
                    'reviewer_id' => null,
                    'reviewer_note' => null,
                    'updated_score' => null,
                    'resolved_at' => null,
                ]
            );

            if ($lecturer) {
                UserNotification::updateOrCreate(
                    [
                        'user_id' => $lecturer->id,
                        'type' => 'complaint_created',
                        'title' => 'Có khiếu nại điểm mới',
                    ],
                    [
                        'message' => 'Một sinh viên vừa gửi khiếu nại điểm cho bài thi đã hoàn tất.',
                        'data' => [
                            'course_section_id' => $schedule->course_section_id,
                            'exam_schedule_id' => $schedule->id,
                        ],
                        'read_at' => null,
                    ]
                );
            }

            $count++;
        }

        $resolvedAttempt = $attempts->skip(1)->first();
        if ($resolvedAttempt) {
            $updatedScore = min(10.0, round(((float) $resolvedAttempt->total_score) + 1.0, 1));
            $updatedCorrectCount = min($totalQuestions, (int) round(($updatedScore / 10) * $totalQuestions));

            Complaint::updateOrCreate(
                [
                    'student_id' => $resolvedAttempt->user_id,
                    'exam_attempt_id' => $resolvedAttempt->id,
                ],
                [
                    'exam_schedule_id' => $schedule->id,
                    'course_section_id' => $schedule->course_section_id,
                    'reason' => 'Xin giảng viên rà soát lại một câu có phương án gây hiểu nhầm.',
                    'current_score' => (float) $resolvedAttempt->total_score,
                    'status' => 'resolved',
                    'reviewer_id' => $lecturer?->id,
                    'reviewer_note' => 'Đã rà soát và điều chỉnh điểm theo đáp án chuẩn đã công bố.',
                    'updated_score' => $updatedScore,
                    'resolved_at' => now()->subDay(),
                ]
            );

            $resolvedAttempt->update([
                'total_score' => $updatedScore,
                'correct_count' => $updatedCorrectCount,
            ]);

            $gradeColumn = GradeColumn::query()
                ->where('course_section_id', $schedule->course_section_id)
                ->where('exam_schedule_id', $schedule->id)
                ->first();

            if ($gradeColumn) {
                StudentGrade::updateOrCreate(
                    [
                        'grade_column_id' => $gradeColumn->id,
                        'student_id' => $resolvedAttempt->user_id,
                    ],
                    [
                        'score' => $updatedScore,
                        'note' => 'Điểm đã cập nhật sau xử lý khiếu nại demo',
                        'updated_by' => $lecturer?->id,
                    ]
                );
            }

            UserNotification::updateOrCreate(
                [
                    'user_id' => $resolvedAttempt->user_id,
                    'type' => 'complaint_updated',
                    'title' => 'Kết quả khiếu nại điểm thi',
                ],
                [
                    'message' => 'Khiếu nại của bạn đã được giảng viên xử lý và cập nhật điểm.',
                    'data' => [
                        'course_section_id' => $schedule->course_section_id,
                        'exam_schedule_id' => $schedule->id,
                    ],
                    'read_at' => null,
                ]
            );

            $count++;
        }

        $reviewingAttempt = $attempts->skip(2)->first();
        if ($reviewingAttempt) {
            Complaint::updateOrCreate(
                [
                    'student_id' => $reviewingAttempt->user_id,
                    'exam_attempt_id' => $reviewingAttempt->id,
                ],
                [
                    'exam_schedule_id' => $schedule->id,
                    'course_section_id' => $schedule->course_section_id,
                    'reason' => 'Em muốn được giải thích thêm về cách chấm ở phần nhiều đáp án.',
                    'current_score' => (float) $reviewingAttempt->total_score,
                    'status' => 'reviewing',
                    'reviewer_id' => $lecturer?->id,
                    'reviewer_note' => 'Giảng viên đang rà soát chi tiết từng câu.',
                    'updated_score' => null,
                    'resolved_at' => null,
                ]
            );

            $count++;
        }

        return $count;
    }
}
