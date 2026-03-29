<?php

namespace App\Services;

use App\Models\Exam;

class LecturerExamQueryService
{
    public function getExamIndexForLecturer(int $lecturerId): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // Đề thi bây giờ thuộc sở hữu trực tiếp của người tạo, và lấy môn học ra để hiển thị
        return Exam::where('created_by', $lecturerId)
            ->with('subject')
            ->latest()
            ->paginate(20);
    }

    // removed getCreateData as it is no longer used

    /**
     * @return array<string, int>
     */
    public function getAttemptStats(Exam $exam): array
    {
        $exam->loadCount([
            'attempts',
            'attempts as completed_attempts_count' => function ($query) {
                $query->where('exam_attempts.status', 'completed');
            }
        ]);

        return [
            'attemptCount' => (int) $exam->attempts_count,
            'completedCount' => (int) $exam->completed_attempts_count,
        ];
    }
}
