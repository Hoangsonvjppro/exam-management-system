<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Enums\ExamAttemptStatus;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

class StudentExamService
{
    public function startAttempt(ExamSchedule $schedule, int $userId, string $ipAddress, ?string $userAgent = null): void
    {
        $exam = $schedule->exam;
        $this->assertStudentCanAccessSchedule($schedule, $userId);

        $now = now();
        $scheduleStart = $schedule->start_datetime;
        $scheduleEnd = $schedule->end_datetime;

        if ($now->lt($scheduleStart)) {
            throw new DomainException('Ca thi chưa bắt đầu.');
        }

        if ($now->gt($scheduleEnd)) {
            throw new DomainException('Ca thi đã kết thúc.');
        }

        $skipLateEntranceChecks =
            $schedule->schedule_mode === \App\Models\ExamSchedule::MODE_IN_RANGE
            || (bool) $schedule->disable_attempt_timer;

        // Logic check vào muộn
        if (!$skipLateEntranceChecks && $now->gt($scheduleStart)) {
            if (!$exam->allow_late_entrance) {
                throw new DomainException('Kỳ thi này không cho phép vào thi muộn.');
            }
            if ($exam->late_entrance_limit_minutes !== null) {
                $limitTime = $scheduleStart->copy()->addMinutes($exam->late_entrance_limit_minutes);
                if ($now->gt($limitTime)) {
                    throw new DomainException("Bạn đã vào muộn quá thời gian cho phép ({$exam->late_entrance_limit_minutes} phút).");
                }
            }
        }

        // // Logic check vắng mặt có phép (Leave Request / Excused)
        // if ($schedule->course_section_id) {
        //     $hasApprovedLeave = \App\Models\LeaveRequest::where('course_section_id', $schedule->course_section_id)
        //         ->where('student_id', $userId)
        //         ->whereDate('date', $schedule->exam_date)
        //         ->where('status', 'approved')
        //         ->exists();

        //     if ($hasApprovedLeave) {
        //         throw new DomainException('Bạn đã được duyệt nghỉ phép vào ngày thi này nên không thể tham gia thi.');
        //     }

        //     $isExcused = \App\Models\AttendanceRecord::where('student_id', $userId)
        //         ->where('status', 'excused')
        //         ->whereHas('session', function ($query) use ($schedule) {
        //             $query->where('course_section_id', $schedule->course_section_id)
        //                   ->whereDate('date', $schedule->exam_date);
        //         })
        //         ->exists();

        //     if ($isExcused) {
        //         throw new DomainException('Bạn đã được đánh dấu vắng mặt có phép trong buổi học này nên không thể tham gia thi.');
        //     }
        // }

        // [Tối ưu] Kiểm tra "Double-check" trước khi vào Transaction/Lock
        $existingInProgress = ExamAttempt::forSchedule($schedule->id)
            ->forUser($userId)
            ->inProgress()
            ->exists();

        if ($existingInProgress) {
            return;
        }

        // [Atomic Lock] Ngăn chặn spam click ở tầng Cache (Redis/Database)
        $lockKey = "start_attempt_{$schedule->id}_{$userId}";
        $lock = Cache::lock($lockKey, 10); // Khóa trong 10 giây

        if (!$lock->get()) {
            throw new DomainException('Yêu cầu bắt đầu thi đang được xử lý, vui lòng đợi giây lát.');
        }

        try {
            DB::transaction(function () use ($schedule, $userId, $ipAddress, $userAgent, $exam) {
                // [Lưu ý] Vẫn dùng lockForUpdate bên trong Transaction để đảm bảo tính Acid
                $attempt = ExamAttempt::forSchedule($schedule->id)
                    ->forUser($userId)
                    ->inProgress()
                    ->lockForUpdate()
                    ->first();

                if ($attempt) {
                    return;
                }

                $hasCompleted = ExamAttempt::forSchedule($schedule->id)
                    ->forUser($userId)
                    ->completed()
                    ->exists();

                if ($hasCompleted && $exam->isOfficial()) {
                    throw new DomainException('Bạn đã hoàn thành bài thi chính thức này. Không thể thi lại.');
                }

                $latestAttempt = ExamAttempt::forSchedule($schedule->id)
                    ->forUser($userId)
                    ->latestAttempt()
                    ->lockForUpdate()
                    ->first();

                $nextNumber = $latestAttempt ? $latestAttempt->attempt_number + 1 : 1;

                try {
                    ExamAttempt::create([
                        'exam_schedule_id' => $schedule->id,
                        'user_id' => $userId,
                        'attempt_number' => $nextNumber,
                        'started_at' => now(),
                        'status' => ExamAttemptStatus::InProgress,
                        'ip_address' => $ipAddress,
                        'user_agent' => substr($userAgent ?? '', 0, 500),
                    ]);
                } catch (UniqueConstraintViolationException $e) {
                    // Nếu lọt qua lock mà vẫn trùng (hi hữu), trả về gracefullly
                    return;
                }
            });
        } finally {
            $lock->release();
        }
    }

    /**
     * @return array{http_code:int,message:string}
     */
    public function saveAnswer(ExamSchedule $schedule, int $userId, array $validated, ?int $tabSwitchCount = null): array
    {
        $attempt = ExamAttempt::forSchedule($schedule->id)
            ->forUser($userId)
            ->inProgress()
            ->first();

        if (! $attempt || $attempt->status !== ExamAttemptStatus::InProgress) {
            return ['http_code' => 403, 'message' => 'Không thể lưu đáp án.'];
        }

        $deadline = $schedule->getDeadlineFor($attempt);
        if (now()->gt($deadline)) {
            return ['http_code' => 403, 'message' => 'Đã hết thời gian làm bài.'];
        }

        // Use transaction for database-level atomicity
        return DB::transaction(function () use ($schedule, $attempt, $validated, $tabSwitchCount) {
            try {
                $isNavigationPing = (bool) ($validated['is_navigation_ping'] ?? false);

                $question = \App\Models\Question::with('questionType')
                    ->where('id', $validated['question_id'])
                    ->first();

                if (! $question) {
                    return ['http_code' => 422, 'message' => 'Câu hỏi không tồn tại.'];
                }

                // Verify if question belongs to the exam
                $questionBelongsToExam = $schedule->exam->questions()
                    ->where('questions.id', $question->id)
                    ->exists();

                if (! $questionBelongsToExam) {
                    return ['http_code' => 422, 'message' => 'Câu hỏi không thuộc đề thi này.'];
                }

                $attemptUpdates = [
                    'current_question_id' => $question->id,
                ];

                if ($tabSwitchCount !== null) {
                    $attemptUpdates['tab_switch_count'] = $tabSwitchCount;
                }

                $attempt->update($attemptUpdates);

                if ($isNavigationPing) {
                    return ['http_code' => 200, 'message' => ''];
                }

                // 1. Update main StudentAnswer (Atomic Upsert)
                $studentAnswer = StudentAnswer::updateOrCreate(
                    [
                        'exam_attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'question_option_id' => $validated['question_option_id'] ?? null,
                        'answer_text' => $validated['answer_text'] ?? null,
                    ]
                );

                // 2. Specialized handling based on question type
                $typeCode = $question->questionType->code;

                if ($typeCode === 'multiple_choice' && isset($validated['option_ids'])) {
                    $newOptionIds = array_map('intval', $validated['option_ids']);

                    // Atomic Sync: Delete options not in the new set, then insert missing ones
                    // Using a transaction (already active) to ensure consistency
                    $studentAnswer->selectedOptions()
                        ->whereNotIn('question_option_id', $newOptionIds)
                        ->delete();

                    foreach ($newOptionIds as $optionId) {
                        // Use firstOrCreate to prevent UniqueConstraintViolation if another request just inserted it
                        $studentAnswer->selectedOptions()->firstOrCreate([
                            'question_option_id' => $optionId
                        ]);
                    }
                }

                return ['http_code' => 200, 'message' => ''];
            } catch (UniqueConstraintViolationException $e) {
                // If a concurrent request already inserted the record, we treat it as success
                return ['http_code' => 200, 'message' => ''];
            } catch (\Exception $e) {
                return ['http_code' => 500, 'message' => 'Lỗi hệ thống khi lưu đáp án.'];
            }
        });
    }

    private function assertStudentCanAccessSchedule(ExamSchedule $schedule, int $userId): void
    {
        if ($schedule->status === 'cancelled') {
            throw new DomainException('Ca thi đã bị hủy.');
        }

        $courseSection = $schedule->courseSection;
        if (! $courseSection) {
            throw new DomainException('Ca thi không hợp lệ.');
        }

        $isEnrolled = $courseSection->students()
            ->where('users.id', $userId)
            ->where('course_section_students.status', EnrollmentService::PIVOT_ENROLLED)
            ->exists();

        if (! $isEnrolled) {
            throw new DomainException('Bạn không thuộc lớp học phần của ca thi này.');
        }

        $hasAssignments = $schedule->scheduleStudents()->exists();
        if (! $hasAssignments) {
            return;
        }

        $isAssigned = $schedule->scheduleStudents()
            ->where('student_id', $userId)
            ->exists();

        if ($isAssigned) {
            return;
        }

        if ($this->canAutoAssignLateEnrolledStudent($schedule, $userId)) {
            $schedule->scheduleStudents()->firstOrCreate(
                ['student_id' => $userId],
                ['attendance_status' => 'pending']
            );

            return;
        }

        throw new DomainException('Bạn chưa được phân vào ca thi này.');
    }

    private function canAutoAssignLateEnrolledStudent(ExamSchedule $schedule, int $userId): bool
    {
        if (! $schedule->created_at) {
            return false;
        }

        $courseSection = $schedule->courseSection;
        if (! $courseSection) {
            return false;
        }

        $enrolledAt = $courseSection->students()
            ->where('users.id', $userId)
            ->where('course_section_students.status', EnrollmentService::PIVOT_ENROLLED)
            ->value('course_section_students.enrolled_at');

        if (! $enrolledAt) {
            return false;
        }

        try {
            return Carbon::parse($enrolledAt)->gt($schedule->created_at);
        } catch (\Throwable) {
            return false;
        }
    }
}
