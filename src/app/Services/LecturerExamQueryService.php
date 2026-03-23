<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\Question;

class LecturerExamQueryService
{
    public function getExamIndexForLecturer(int $lecturerId): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Exam::createdBy($lecturerId)
            ->with('subject')
            ->withCount('questions')
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
            'attempts as completed_attempts_count' => fn($query) => $query->completed(),
        ]);

        return [
            'attemptCount' => (int) $exam->attempts_count,
            'completedCount' => (int) $exam->completed_attempts_count,
        ];
    }
}
