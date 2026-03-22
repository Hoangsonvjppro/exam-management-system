<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\CourseSection;
use App\Models\Subject;
use App\Models\User;

class LandingPageService
{
    /**
     * @return array<string, mixed>
     */
    public function getLandingData(): array
    {
        return [
            'studentCount' => User::role('student')->count(),
            'lecturerCount' => User::role('lecturer')->count(),
            'subjectCount' => Subject::count(),
            'sectionCount' => CourseSection::active()->count(),
            'announcements' => Announcement::published()
                ->latest()
                ->limit(3)
                ->get(),
        ];
    }
}
