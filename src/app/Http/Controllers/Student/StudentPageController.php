<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\User;
use App\Services\StudentDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class StudentPageController extends Controller
{
    public function __construct(
        private readonly StudentDashboardService $studentDashboardService,
        private readonly \App\Services\StudentResultService $studentResultService
    ) {}

    public function classes(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('student.classes.index', $this->studentDashboardService->getClassesData($user));
    }

    /**
     * Class Workspace — 3-tab detail view for a single course section.
     */
    public function classShow(CourseSection $section): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Verify student is enrolled
        $isEnrolled = $user->enrolledSections()->where('course_sections.id', $section->id)->exists();
        abort_unless($isEnrolled, 403, 'Bạn không thuộc lớp học phần này.');

        $data = $this->studentDashboardService->getClassShowData($user, $section);

        return view('student.classes.show', $data);
    }

    public function results(\Illuminate\Http\Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $semesterId = $request->input('semester_id');

        $data = $this->studentResultService->getResultsData($user, $semesterId ? (int) $semesterId : null);

        return view('student.results.index', $data);
    }

    public function attendance(): View
    {
        return view('student.attendance.index-placeholder');
    }

    public function complaints(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $complaints = \App\Models\Complaint::where('student_id', $user->id)
            ->with(['schedule.exam', 'section', 'reviewer'])
            ->latest()
            ->paginate(15);

        return view('student.complaints.index', compact('complaints'));
    }
}
