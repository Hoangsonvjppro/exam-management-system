<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\ExamMatrix;
use Illuminate\Support\Facades\DB;

class ExamService
{
    public function __construct(
        private readonly ExamGenerationService $examGenerationService
    ) {}
    /**
     * Tạo đề thi mới kèm đồng bộ câu hỏi.
     *
     * @param CourseSection $courseSection
     * @param array $data Validated data từ StoreExamRequest
     * @return Exam
     *
     * @throws \RuntimeException
     */
    public function createExam(CourseSection $courseSection, array $data): Exam
    {
        return DB::transaction(function () use ($courseSection, $data) {
            $exam = $courseSection->exams()->create($data);

            $this->syncQuestions($exam, $data['question_ids']);

            return $exam;
        });
    }

    /**
     * Tạo đề thi từ ma trận cấu trúc (auto-generate).
     *
     * @param CourseSection $courseSection
     * @param array $data Validated exam data
     * @param array $matrixData Mảng [{chapter_id, difficulty, question_type_id, question_count, points_each}]
     * @return Exam
     */
    public function createExamFromMatrix(CourseSection $courseSection, array $data, array $matrixData): Exam
    {
        return DB::transaction(function () use ($courseSection, $data, $matrixData) {
            $exam = $courseSection->exams()->create($data);

            // Lưu matrix rows
            foreach ($matrixData as $row) {
                ExamMatrix::create([
                    'exam_id' => $exam->id,
                    'chapter_id' => $row['chapter_id'] ?? null,
                    'difficulty' => $row['difficulty'],
                    'question_type_id' => $row['question_type_id'] ?? null,
                    'question_count' => $row['question_count'],
                    'points_each' => $row['points_each'] ?? 1.00,
                ]);
            }

            // Sinh câu hỏi tự động từ ma trận
            $matrixRows = $exam->matrices()->with('chapter')->get();
            $this->examGenerationService->generateFromMatrix($exam, $matrixRows);

            return $exam;
        });
    }

    /**
     * Cập nhật đề thi, kiểm tra canEditStructure trước khi sync câu hỏi.
     *
     * @param Exam $exam
     * @param array $data Validated data từ UpdateExamRequest
     * @return Exam
     *
     * @throws \RuntimeException
     */
    public function updateExam(Exam $exam, array $data): Exam
    {
        return DB::transaction(function () use ($exam, $data) {
            // Nếu đã có SV thi, chỉ cho sửa metadata (tên, mô tả, cấu hình hiển thị)
            if (! $exam->canEditStructure()) {
                $data = collect($data)->only([
                    'title',
                    'description',
                    'show_score_after_submit',
                    'show_answers_after_submit',
                ])->toArray();
            } else {
                // Update questions only if structure is editable
                $questionIds = $data['question_ids'] ?? [];
                if (! empty($questionIds)) {
                    $this->syncQuestions($exam, $questionIds);
                }

                $totalPoints = $exam->examQuestions()->sum('points');
                $data['total_points'] = $totalPoints;
                $data['pass_points'] = min($exam->pass_points ?? 0, $totalPoints);
            }

            $exam->update($data);

            return $exam;
        });
    }

    /**
     * Xoá đề thi: hard-delete nếu chưa có attempt, soft-delete nếu đã có.
     *
     * @param Exam $exam
     * @return string Message mô tả hành động đã thực hiện
     */
    public function deleteExam(Exam $exam): string
    {
        if ($exam->attempts()->exists()) {
            $exam->delete(); // soft-delete
            return 'Đề thi đã được lưu trữ (xoá mềm) vì đã có sinh viên thi.';
        }

        // Hard-delete
        $exam->questions()->detach();
        $exam->forceDelete();

        return 'Đề thi đã được xoá vĩnh viễn.';
    }

    /**
     * Chuyển trạng thái publish (draft → published).
     *
     * @throws \DomainException
     */
    public function publishExam(Exam $exam): void
    {
        if (! $exam->canTransitionTo(\App\Enums\ExamStatus::Published)) {
            throw new \DomainException('Không thể mở đề thi từ trạng thái "' . $exam->status->value . '".');
        }

        if ($exam->questions()->count() === 0) {
            throw new \DomainException('Đề kiểm tra phải có ít nhất một câu hỏi.');
        }

        $exam->update(['status' => \App\Enums\ExamStatus::Published]);
    }

    /**
     * Đóng đề thi (published → closed).
     *
     * @throws \DomainException
     */
    public function closeExam(Exam $exam): void
    {
        if (! $exam->canTransitionTo(\App\Enums\ExamStatus::Closed)) {
            throw new \DomainException('Không thể đóng đề thi từ trạng thái "' . $exam->status->value . '".');
        }

        $exam->update(['status' => \App\Enums\ExamStatus::Closed]);
    }

    /**
     * Mở lại đề thi (closed → published) — yêu cầu lý do.
     *
     * @throws \DomainException
     */
    public function reopenExam(Exam $exam, string $reason): void
    {
        if ($exam->status !== \App\Enums\ExamStatus::Closed) {
            throw new \DomainException('Chỉ có thể mở lại đề thi đã đóng.');
        }

        $exam->update([
            'status'        => \App\Enums\ExamStatus::Published,
            'reopen_reason' => $reason,
        ]);
    }

    /**
     * Đồng bộ câu hỏi vào đề thi, tạo snapshot và tính lại total_points.
     */
    private function syncQuestions(Exam $exam, array $questionIds): void
    {
        // Xoá câu hỏi cũ
        $exam->examQuestions()->delete();

        // Lấy tất cả câu hỏi cần sync kèm options
        $questions = \App\Models\Question::with('options')
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        foreach ($questionIds as $index => $questionId) {
            $question = $questions->get($questionId);
            if (!$question) continue;

            // Tạo snapshot JSON chứa nội dung câu hỏi + options
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

            \App\Models\ExamQuestion::create([
                'exam_id' => $exam->id,
                'question_id' => $questionId,
                'points' => 1.00,
                'order_index' => $index + 1,
                'question_snapshot' => $snapshot,
            ]);
        }

        // Đồng bộ total_points theo tổng điểm câu hỏi thực tế
        $totalPoints = $exam->examQuestions()->sum('points');
        $exam->update([
            'total_points' => $totalPoints,
            'pass_points'  => min($exam->pass_points ?? 0, $totalPoints),
        ]);
    }
}
