<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LecturerDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class LecturerPageController extends Controller
{
    public function __construct(private readonly LecturerDashboardService $lecturerDashboardService) {}

    public function dashboard(): View
    {
        /** @var User $lecturer */
        $lecturer = Auth::user();

        return view('lecturer.dashboard', $this->lecturerDashboardService->getDashboardData($lecturer));
    }

    public function questions(): View
    {
        return view('lecturer.questions.index');
    }

    public function attendance(): View
    {
        return view('lecturer.attendance.index-placeholder');
    }
}
