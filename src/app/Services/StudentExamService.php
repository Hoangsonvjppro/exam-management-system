<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use DomainException;

class StudentExamService
{
    public function startAttempt(Exam $exam, int $userId, string $ipAddress, ?string $userAgent = null): void
    {
        $now = now();
        if ($exam->start_time && $now->lt($exam->start_time)) {
            throw new DomainException('Bài thi chưa bắt đầu.');
        }

        if ($exam->end_time && $now->gt($exam->end_time)) {
            throw new DomainException('Bài thi đã kết thúc.');
        }

        $attempt = ExamAttempt::forExam($exam->id)
            ->forUser($userId)
            ->inProgress()
            ->first();

        if ($attempt) {
            return;
        }

        $hasCompleted = ExamAttempt::forExam($exam->id)
            ->forUser($userId)
            ->completed()
            ->exists();

        if ($hasCompleted && $exam->isOfficial()) {
            throw new DomainException('Bạn đã hoàn thành bài thi chính thức này. Không thể thi lại.');
        }

        $latestAttempt = ExamAttempt::forExam($exam->id)
            ->forUser($userId)
            ->latestAttempt()
            ->first();

        $nextNumber = $latestAttempt ? $latestAttempt->attempt_number + 1 : 1;

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $userId,
            'attempt_number' => $nextNumber,
            'started_at' => now(),
            'status' => 'in_progress',
            'ip_address' => $ipAddress,
            'user_agent' => substr($userAgent ?? '', 0, 500),
        ]);
    }

    /**
     * @return array{http_code:int,message:string}
     */
    public function saveAnswer(Exam $exam, int $userId, array $validated, ?int $tabSwitchCount = null): array
    {
        $attempt = ExamAttempt::forExam($exam->id)
            ->forUser($userId)
            ->inProgress()
            ->first();

        if (! $attempt || $attempt->status !== 'in_progress') {
            return ['http_code' => 403, 'message' => 'Không thể lưu đáp án.'];
        }

        $deadline = $exam->getDeadlineFor($attempt);
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
