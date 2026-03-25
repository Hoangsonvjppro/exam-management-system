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

        $schedules = \App\Models\ExamSchedule::whereIn('course_section_id', $sectionIds)
            ->whereHas('exam', function ($query) {
                $query->published();
            })
            ->with(['exam.subject'])
            ->orderByDesc('exam_date')
            ->orderByDesc('start_time')
            ->get();

        return [
            'enrolledSections' => $enrolledSections,
            'schedules' => $schedules,
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
