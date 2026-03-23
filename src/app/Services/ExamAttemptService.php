<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Enums\ExamAttemptStatus;
use Illuminate\Support\Facades\DB;

class ExamAttemptService
{
    /**
     * Finalize một attempt: upsert đáp án cuối, chấm điểm, cập nhật trạng thái.
     * Idempotent: nếu đã completed thì return sớm.
     *
     * @param ExamAttempt $attempt
     * @param array|null $lastAnswers  Mảng [question_id => question_option_id] từ form submit
     */
    public function finalizeAttempt(ExamAttempt $attempt, ?array $lastAnswers = null): void
    {
        // Idempotent: đã hoàn thành thì không chấm lại
        if ($attempt->status === ExamAttemptStatus::Completed) {
            return;
        }

        DB::transaction(function () use ($attempt, $lastAnswers) {
            $schedule = $attempt->schedule;
            if (!$schedule || !$schedule->exam) {
                return;
            }

            $exam = $schedule->exam;

            // Pre-load all questions in this exam to avoid N+1 queries in loops
            $examQuestions = $exam->examQuestions()
                ->get()
                ->keyBy('question_id');
            
            $validQuestionIds = $examQuestions->keys()->all();

            // 1. Upsert đáp án cuối cùng từ payload submit (Medium #19)
            if ($lastAnswers) {
                // Pre-load options to validate in one query
                $optionIds = array_values($lastAnswers);
                $validOptions = QuestionOption::whereIn('id', $optionIds)
                    ->get()
                    ->groupBy('question_id');

                foreach ($lastAnswers as $questionId => $optionId) {
                    // Check if question belongs to the exam
                    $qId = (int) $questionId;
                    $optId = (int) $optionId;

                    if (!in_array($qId, $validQuestionIds)) {
                        continue;
                    }

                    // Check if option belongs to the question
                    $questionOptions = $validOptions->get($qId);
                    $isOptionValid = $questionOptions && $questionOptions->contains('id', $optId);

                    if ($isOptionValid) {
                        StudentAnswer::updateOrCreate(
                            [
                                'exam_attempt_id' => $attempt->id,
                                'question_id'     => $qId,
                            ],
                            [
                                'question_option_id' => $optId,
                            ]
                        );
                    }
                }
            }

            // 2. Chấm điểm toàn bộ answers
            $answers = $attempt->answers()->with('option')->get();
            $totalScore = 0;

            foreach ($answers as $answer) {
                $isCorrect = $answer->option?->is_correct ?? false;

                // Lấy điểm từ pre-loaded data examQuestions (pivot)
                $point = $examQuestions->get($answer->question_id)?->points ?? 1.00;

                $awardedPoint = $isCorrect ? $point : 0;
                $totalScore += $awardedPoint;

                $answer->update([
                    'is_correct'     => $isCorrect,
                    'points_awarded' => $awardedPoint,
                ]);
            }

            // 3. Cập nhật trạng thái attempt + submitted_answers_count
            $attempt->update([
                'status'                  => ExamAttemptStatus::Completed,
                'completed_at'            => now(),
                'total_score'             => $totalScore,
                'submitted_answers_count' => $answers->count(),
            ]);
        });
    }
}
