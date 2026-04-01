<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\CourseSection;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class LandingPageService
{
    /**
     * @return array<string, mixed>
     */
    public function getLandingData(): array
    {
        $studentCount = 0;
        $lecturerCount = 0;
        $subjectCount = 0;
        $sectionCount = 0;
        $announcements = collect();

        try {
            if (
                Schema::hasTable('users')
                && Schema::hasTable('roles')
                && Schema::hasTable('model_has_roles')
            ) {
                $studentCount = User::role('student')->count();
                $lecturerCount = User::role('lecturer')->count();
            }

            if (Schema::hasTable('subjects')) {
                $subjectCount = Subject::count();
            }

            if (Schema::hasTable('course_sections')) {
                $sectionCount = CourseSection::active()->count();
            }

            if (Schema::hasTable('announcements')) {
                $announcements = Announcement::published()
                    ->latest()
                    ->limit(3)
                    ->get();
            }
        } catch (QueryException) {
            // Keep safe defaults if schema is not ready.
        }

        return [
            'studentCount' => $studentCount,
            'lecturerCount' => $lecturerCount,
            'subjectCount' => $subjectCount,
            'sectionCount' => $sectionCount,
            'announcements' => $announcements,
        ];
    }
}
