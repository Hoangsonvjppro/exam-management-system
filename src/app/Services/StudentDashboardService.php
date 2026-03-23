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
        $enrolledSections = $user->enrolledSections()->withCount('students')->with('lecturer')->get();
        $sectionIds = $enrolledSections->pluck('id');

        $exams = Exam::whereHas('schedules', function ($query) use ($sectionIds) {
            $query->whereIn('course_section_id', $sectionIds);
        })
            ->published()
            ->with('subject')
            ->orderByDesc('created_at')
            ->get();

        return [
            'enrolledSections' => $enrolledSections,
            'exams' => $exams,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getClassesData(User $user): array
    {
        return [
            'enrolledSections' => $user->enrolledSections()
                ->withCount('students')
                ->with('lecturer')
                ->get(),
        ];
    }
}
