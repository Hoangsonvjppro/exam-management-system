<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $enrolledSections = $user->enrolledSections()->with('lecturer')->get();
        $sectionIds = $enrolledSections->pluck('id');

        $exams = Exam::whereIn('course_section_id', $sectionIds)
            ->where('status', 'published')
            ->with('courseSection')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.dashboard', compact('enrolledSections', 'exams'));
    }
}
