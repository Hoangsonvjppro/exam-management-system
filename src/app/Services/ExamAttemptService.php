<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Models\GradeColumn;
use App\Models\StudentGrade;
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
            $answers = $attempt->answers()->with('selectedOptions')->get();
            $correctCount = 0;
            $totalCorrectPoints = 0;
            $totalQuestions = $examQuestions->count();
            
            $scoringMethod = $exam->multiple_choice_scoring_method ?? 'all_or_nothing';

            foreach ($answers as $answer) {
                $examQuestion = $examQuestions->get($answer->question_id);
                if (!$examQuestion || !$examQuestion->question_snapshot) {
                    continue;
                }

                $snapshot = $examQuestion->question_snapshot;
                $options = collect($snapshot['options'] ?? []);
                $typeCode = $snapshot['question_type_code'] ?? null;
                
                if ($typeCode === 'multiple_choice') {
                    $selectedIds = $answer->selectedOptions->pluck('question_option_id')->toArray();
                    $correctOptionIds = $options->where('is_correct', true)->pluck('id')->toArray();
                    
                    if ($scoringMethod === 'all_or_nothing') {
                        $isCorrect = count($selectedIds) === count($correctOptionIds) && 
                                     empty(array_diff($selectedIds, $correctOptionIds)) && 
                                     empty(array_diff($correctOptionIds, $selectedIds));
                        $awardedPoint = $isCorrect ? 1 : 0;
                    } else { // partial_credit
                        $correctPicks = 0;
                        $incorrectPicks = 0;
                        foreach ($selectedIds as $selectedId) {
                            if (in_array($selectedId, $correctOptionIds)) {
                                $correctPicks++;
                            } else {
                                $incorrectPicks++;
                            }
                        }
                        $totalCorrectOptions = count($correctOptionIds);
                        $awardedPoint = $totalCorrectOptions > 0 
                            ? max(0, ($correctPicks - $incorrectPicks) / $totalCorrectOptions) 
                            : 0;
                        $isCorrect = $awardedPoint > 0;
                    }
                } else {
                    // Kiểm tra đáp án sinh viên chọn có đúng theo snapshot không
                    $selectedOption = $options->firstWhere('id', $answer->question_option_id);
                    $isCorrect = $selectedOption['is_correct'] ?? false;
                    $awardedPoint = $isCorrect ? 1 : 0;
                }

                $totalCorrectPoints += $awardedPoint;
                if ($awardedPoint == 1) {
                    $correctCount++;
                }

                $answer->update([
                    'is_correct'     => $isCorrect,
                    'points_awarded' => $awardedPoint,
                ]);
            }

            // 3. Tính điểm hệ 10: (tổng điểm thành phần / tổng câu) × 10, làm tròn 1 số thập phân
            $totalScore = $totalQuestions > 0
                ? round(($totalCorrectPoints / $totalQuestions) * 10, 1)
                : 0;

            // 4. Cập nhật trạng thái attempt
            $attempt->update([
                'status'                  => ExamAttemptStatus::Completed,
                'completed_at'            => now(),
                'total_score'             => $totalScore,
                'correct_count'           => $correctCount,
                'submitted_answers_count' => $answers->count(),
            ]);

            // 5. Tự động đồng bộ điểm thi vào bảng Điểm quá trình nếu cấu hình
            $gradeColumn = GradeColumn::where('exam_schedule_id', $schedule->id)
                ->where('is_exam_linked', true)
                ->first();

            if ($gradeColumn) {
                StudentGrade::updateOrCreate(
                    [
                        'grade_column_id' => $gradeColumn->id,
                        'student_id'      => $attempt->user_id,
                    ],
                    [
                        'score' => $totalScore,
                        'note'  => 'Đồng bộ tự động từ hệ thống chấm điểm',
                    ]
                );
            }
        });
    }
}
