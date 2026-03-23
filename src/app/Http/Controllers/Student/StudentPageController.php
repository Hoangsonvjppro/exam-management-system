<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class StudentPageController extends Controller
{
    public function __construct(private readonly StudentDashboardService $studentDashboardService) {}

    public function classes(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('student.classes.index', $this->studentDashboardService->getClassesData($user));
    }

    public function results(): View
    {
        return view('student.results.index-placeholder');
    }

    public function attendance(): View
    {
        return view('student.attendance.index-placeholder');
    }
}
