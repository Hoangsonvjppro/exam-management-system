<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class ExamService
{
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
                    'title', 'description',
                    'show_score_after_submit', 'show_answers_after_submit',
                ])->toArray();
            } else {
                // Update questions only if structure is editable
                $questionIds = $data['question_ids'] ?? [];
                if (! empty($questionIds)) {
                    $this->syncQuestions($exam, $questionIds);
                }

                $totalPoints = $exam->questions()->sum('exam_questions.points');
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
        if (! $exam->canTransitionTo('published')) {
            throw new \DomainException('Không thể mở đề thi từ trạng thái "' . $exam->status . '".');
        }

        if ($exam->questions()->count() === 0) {
            throw new \DomainException('Đề kiểm tra phải có ít nhất một câu hỏi.');
        }

        $exam->update(['status' => 'published']);
    }

    /**
     * Đóng đề thi (published → closed).
     *
     * @throws \DomainException
     */
    public function closeExam(Exam $exam): void
    {
        if (! $exam->canTransitionTo('closed')) {
            throw new \DomainException('Không thể đóng đề thi từ trạng thái "' . $exam->status . '".');
        }

        $exam->update(['status' => 'closed']);
    }

    /**
     * Mở lại đề thi (closed → published) — yêu cầu lý do.
     *
     * @throws \DomainException
     */
    public function reopenExam(Exam $exam, string $reason): void
    {
        if ($exam->status !== 'closed') {
            throw new \DomainException('Chỉ có thể mở lại đề thi đã đóng.');
        }

        $exam->update([
            'status'        => 'published',
            'reopen_reason' => $reason,
        ]);
    }

    /**
     * Đồng bộ câu hỏi vào đề thi và tính lại total_points.
     */
    private function syncQuestions(Exam $exam, array $questionIds): void
    {
        $questionsData = collect($questionIds)->mapWithKeys(function ($id, $index) {
            return [$id => ['points' => 1.00, 'order_index' => $index + 1]];
        })->all();

        $exam->questions()->sync($questionsData);

        // Đồng bộ total_points theo tổng điểm câu hỏi thực tế
        $totalPoints = $exam->questions()->sum('exam_questions.points');
        $exam->update([
            'total_points' => $totalPoints,
            'pass_points'  => min($exam->pass_points ?? 0, $totalPoints),
        ]);
    }
}
