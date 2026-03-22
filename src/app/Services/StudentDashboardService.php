<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\User;

class StudentDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(User $user): array
    {
        $enrolledSections = $user->enrolledSections()->with('lecturer')->get();
        $sectionIds = $enrolledSections->pluck('id');

        $exams = Exam::whereIn('course_section_id', $sectionIds)
            ->published()
            ->with('courseSection')
            ->orderByDesc('created_at')
            ->get();

        return [
            'enrolledSections' => $enrolledSections,
            'exams' => $exams,
        ];
    }
}
