<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CourseSection;
use App\Models\Subject;
use App\Models\User;

class LandingController extends Controller
{
    public function __invoke()
    {
        $studentCount  = User::role('student')->count();
        $lecturerCount = User::role('lecturer')->count();
        $subjectCount  = Subject::count();
        $sectionCount  = CourseSection::where('status', 'active')->count();

        $announcements = Announcement::published()
            ->latest()
            ->limit(3)
            ->get();

        return view('welcome', compact(
            'studentCount',
            'lecturerCount',
            'subjectCount',
            'sectionCount',
            'announcements',
        ));
    }
}
