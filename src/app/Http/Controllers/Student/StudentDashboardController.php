<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function __construct(private readonly StudentDashboardService $studentDashboardService) {}

    public function __invoke(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('student.dashboard', $this->studentDashboardService->getDashboardData($user));
    }
}
