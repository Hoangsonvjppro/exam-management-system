<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use Illuminate\Support\Collection;

class ExamGenerationService
{
    /**
     * Sinh đề thi tự động từ ma trận cấu trúc.
     *
     * Dựa trên từng row (chapter × difficulty × question_type),
     * query ngân hàng câu hỏi approved, random pick, sync vào exam_questions kèm snapshot.
     *
     * @param Exam $exam
     * @param Collection $matrixRows Collection of ExamMatrix rows
     * @throws \RuntimeException Khi ngân hàng không đủ câu hỏi
     */
    public function generateFromMatrix(Exam $exam, Collection $matrixRows): void
    {
        // Lấy subject_id từ course section
        $subjectId = $exam->courseSection->subject_id;

        // Xoá câu hỏi cũ
        $exam->examQuestions()->delete();

        $orderIndex = 1;
        $usedQuestionIds = [];

        foreach ($matrixRows as $row) {
            $query = Question::with('options')
                ->where('subject_id', $subjectId)
                ->where('status', 'approved')
                ->where('difficulty', $row->difficulty)
                ->whereNotIn('id', $usedQuestionIds);

            if ($row->chapter_id) {
                $query->where('chapter_id', $row->chapter_id);
            }

            if ($row->question_type_id) {
                $query->where('question_type_id', $row->question_type_id);
            }

            $questions = $query->inRandomOrder()
                ->limit($row->question_count)
                ->get();

            if ($questions->count() < $row->question_count) {
                $chapterName = $row->chapter?->name ?? 'Tất cả chương';
                throw new \RuntimeException(
                    "Không đủ câu hỏi cho: {$chapterName} - {$row->difficulty}. " .
                    "Cần {$row->question_count}, có {$questions->count()}."
                );
            }

            foreach ($questions as $question) {
                $snapshot = [
                    'id' => $question->id,
                    'content' => $question->content,
                    'difficulty' => $question->difficulty,
                    'explanation' => $question->explanation,
                    'options' => $question->options->map(fn($opt) => [
                        'id' => $opt->id,
                        'label' => $opt->label,
                        'content' => $opt->content,
                        'is_correct' => $opt->is_correct,
                        'order' => $opt->order,
                    ])->toArray(),
                ];

                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $question->id,
                    'points' => $row->points_each,
                    'order_index' => $orderIndex++,
                    'question_snapshot' => $snapshot,
                ]);

                $usedQuestionIds[] = $question->id;
            }
        }

        // Cập nhật total_points
        $totalPoints = $exam->examQuestions()->sum('points');
        $exam->update([
            'total_points' => $totalPoints,
            'pass_points' => min($exam->pass_points ?? 0, $totalPoints),
        ]);
    }

    /**
     * Kiểm tra ngân hàng câu hỏi có đủ cho ma trận không.
     *
     * @return array Danh sách các row thiếu câu hỏi: [{row, required, available}]
     */
    public function validateMatrix(int $subjectId, array $matrixData): array
    {
        $shortages = [];

        foreach ($matrixData as $row) {
            $query = Question::where('subject_id', $subjectId)
                ->where('status', 'approved')
                ->where('difficulty', $row['difficulty']);

            if (!empty($row['chapter_id'])) {
                $query->where('chapter_id', $row['chapter_id']);
            }

            if (!empty($row['question_type_id'])) {
                $query->where('question_type_id', $row['question_type_id']);
            }

            $available = $query->count();
            $required = $row['question_count'];

            if ($available < $required) {
                $shortages[] = [
                    'chapter_id' => $row['chapter_id'] ?? null,
                    'difficulty' => $row['difficulty'],
                    'question_type_id' => $row['question_type_id'] ?? null,
                    'required' => $required,
                    'available' => $available,
                ];
            }
        }

        return $shortages;
    }
}
