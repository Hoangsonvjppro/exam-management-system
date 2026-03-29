<?php

namespace App\Services;

use App\Models\User;

class LecturerDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(User $lecturer): array
    {
        $mySections = $lecturer->courseSections()->withCount('students')->get();

        return [
            'lecturer' => $lecturer,
            'mySections' => $mySections,
            'activeCount' => $mySections->where('status', 'active')->count(),
            'studentTotal' => $mySections->sum('students_count'),
            'questionCount' => $lecturer->questions()->count(),
        ];
    }
}
