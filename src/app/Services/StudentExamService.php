<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Enums\ExamAttemptStatus;
use DomainException;

class StudentExamService
{
    public function startAttempt(ExamSchedule $schedule, int $userId, string $ipAddress, ?string $userAgent = null): void
    {
        $exam = $schedule->exam;
        $now = now();
        $scheduleStart = \Carbon\Carbon::parse($schedule->exam_date->format('Y-m-d') . ' ' . $schedule->start_time);
        $scheduleEnd = \Carbon\Carbon::parse($schedule->exam_date->format('Y-m-d') . ' ' . $schedule->end_time);

        if ($now->lt($scheduleStart)) {
            throw new DomainException('Ca thi chưa bắt đầu.');
        }

        if ($now->gt($scheduleEnd)) {
            throw new DomainException('Ca thi đã kết thúc.');
        }

        // Logic check vào muộn
        if ($now->gt($scheduleStart)) {
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

        $attempt = ExamAttempt::forSchedule($schedule->id)
            ->forUser($userId)
            ->inProgress()
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
            ->first();

        $nextNumber = $latestAttempt ? $latestAttempt->attempt_number + 1 : 1;

        ExamAttempt::create([
            'exam_schedule_id' => $schedule->id,
            'user_id' => $userId,
            'attempt_number' => $nextNumber,
            'started_at' => now(),
            'status' => ExamAttemptStatus::InProgress,
            'ip_address' => $ipAddress,
            'user_agent' => substr($userAgent ?? '', 0, 500),
        ]);
    }

    /**
     * @return array{http_code:int,message:string}
     */
    public function saveAnswer(ExamSchedule $schedule, int $userId, array $validated, ?int $tabSwitchCount = null): array
    {
        $exam = $schedule->exam;
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

        $questionBelongsToExam = $exam->questions()
            ->where('questions.id', $validated['question_id'])
            ->exists();

        if (! $questionBelongsToExam) {
            return ['http_code' => 422, 'message' => 'Câu hỏi không thuộc đề thi này.'];
        }

        $optionBelongsToQuestion = QuestionOption::where('id', $validated['question_option_id'])
            ->where('question_id', $validated['question_id'])
            ->exists();

        if (! $optionBelongsToQuestion) {
            return ['http_code' => 422, 'message' => 'Đáp án không thuộc câu hỏi này.'];
        }

        StudentAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $validated['question_id'],
            ],
            [
                'question_option_id' => $validated['question_option_id'],
            ]
        );

        if ($tabSwitchCount !== null) {
            $attempt->update([
                'tab_switch_count' => $tabSwitchCount,
            ]);
        }

        return ['http_code' => 200, 'message' => ''];
    }
}
