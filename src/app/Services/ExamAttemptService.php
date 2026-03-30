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
     * Finalize một attempt: chấm điểm dựa trên snapshot và cập nhật trạng thái.
     * Idempotent: nếu đã completed thì return sớm.
     *
     * @param ExamAttempt $attempt
     */
    public function finalizeAttempt(ExamAttempt $attempt): void
    {
        // Idempotent: đã hoàn thành thì không chấm lại
        if ($attempt->status === ExamAttemptStatus::Completed) {
            return;
        }

        DB::transaction(function () use ($attempt) {
            $schedule = $attempt->schedule;
            if (!$schedule || !$schedule->exam) {
                return;
            }

            $exam = $schedule->exam;

            // 1. Lấy toàn bộ snapshot câu hỏi của đề thi này
            $examQuestions = $exam->examQuestions()
                ->get()
                ->keyBy('question_id');
            
            // 2. Chấm điểm toàn bộ answers dựa trên snapshot
            $answers = $attempt->answers()->get();
            $correctCount = 0;
            $totalQuestions = $examQuestions->count();

            foreach ($answers as $answer) {
                $examQuestion = $examQuestions->get($answer->question_id);
                if (!$examQuestion || !$examQuestion->question_snapshot) {
                    continue;
                }

                $snapshot = $examQuestion->question_snapshot;
                $options = collect($snapshot['options'] ?? []);
                
                // Kiểm tra đáp án sinh viên chọn có đúng theo snapshot không
                $selectedOption = $options->firstWhere('id', $answer->question_option_id);
                $isCorrect = $selectedOption['is_correct'] ?? false;

                $awardedPoint = $isCorrect ? 1 : 0;
                if ($isCorrect) {
                    $correctCount++;
                }

                $answer->update([
                    'is_correct'     => $isCorrect,
                    'points_awarded' => $awardedPoint,
                ]);
            }

            // 3. Tính điểm hệ 10: (số câu đúng / tổng câu) × 10, làm tròn 1 số thập phân
            $totalScore = $totalQuestions > 0
                ? round(($correctCount / $totalQuestions) * 10, 1)
                : 0;

            // 4. Cập nhật trạng thái attempt
            $attempt->update([
                'status'                  => ExamAttemptStatus::Completed,
                'completed_at'            => now(),
                'total_score'             => $totalScore,
                'correct_count'           => $correctCount,
                'submitted_answers_count' => $answers->count(),
            ]);
        });
    }
}
