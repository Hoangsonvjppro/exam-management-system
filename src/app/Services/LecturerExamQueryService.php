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
        return Exam::whereHas('courseSection', function ($query) use ($lecturerId) {
            $query->where('lecturer_id', $lecturerId);
        })->with('courseSection')
            ->latest()
            ->paginate(20);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCreateData(CourseSection $courseSection): array
    {
        return [
            'questions' => Question::approvedForSubject($courseSection->subject_id)->get(),
            'chapters' => Chapter::where('subject_id', $courseSection->subject_id)
                ->orderBy('order')
                ->get(),
        ];
    }

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
