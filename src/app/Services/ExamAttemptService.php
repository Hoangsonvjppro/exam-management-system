<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
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
        if ($attempt->status === ExamAttempt::STATUS_COMPLETED) {
            return;
        }

        DB::transaction(function () use ($attempt, $lastAnswers) {
            $exam = $attempt->exam;

            // 1. Upsert đáp án cuối cùng từ payload submit (Medium #19)
            if ($lastAnswers) {
                foreach ($lastAnswers as $questionId => $optionId) {
                    // Chỉ upsert nếu option thuộc question và question thuộc exam
                    $questionInExam = $exam->questions()
                        ->where('questions.id', $questionId)
                        ->exists();

                    $optionValid = QuestionOption::where('id', $optionId)
                        ->where('question_id', $questionId)
                        ->exists();

                    if ($questionInExam && $optionValid) {
                        StudentAnswer::updateOrCreate(
                            [
                                'exam_attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                            ],
                            [
                                'question_option_id' => $optionId,
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

                // Lấy điểm từ exam_questions (pivot) thay vì dùng questions()
                $examQuestion = $exam->examQuestions()
                    ->where('question_id', $answer->question_id)
                    ->first();
                $point = $examQuestion?->points ?? 1.00;

                $awardedPoint = $isCorrect ? $point : 0;
                $totalScore += $awardedPoint;

                $answer->update([
                    'is_correct'     => $isCorrect,
                    'points_awarded' => $awardedPoint,
                ]);
            }

            // 3. Cập nhật trạng thái attempt + submitted_answers_count
            $attempt->update([
                'status'                  => \App\Enums\ExamAttemptStatus::Completed,
                'completed_at'            => now(),
                'total_score'             => $totalScore,
                'submitted_answers_count' => $answers->count(),
            ]);
        });
    }
}
