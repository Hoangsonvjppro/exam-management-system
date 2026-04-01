<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class LecturerDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(User $lecturer): array
    {
        $mySections = collect();
        $questionCount = 0;

        try {
            if (
                Schema::hasTable('course_sections')
                && Schema::hasTable('course_section_students')
                && Schema::hasTable('users')
            ) {
                $mySections = $lecturer->courseSections()->withCount('students')->get();
            } elseif (Schema::hasTable('course_sections')) {
                $mySections = $lecturer->courseSections()->get();
            }

            if (Schema::hasTable('questions')) {
                $questionCount = $lecturer->questions()->count();
            }
        } catch (QueryException) {
            // Keep safe defaults if schema is not ready.
        }

        return [
            'lecturer' => $lecturer,
            'mySections' => $mySections,
            'activeCount' => $mySections->where('status', 'active')->count(),
            'studentTotal' => $mySections->sum('students_count'),
            'questionCount' => $questionCount,
        ];
    }
}
