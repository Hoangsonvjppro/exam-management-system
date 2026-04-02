<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $classes = $user->enrolledSections()
            ->withCount('students')
            ->with(['lecturer', 'subject', 'semester'])
            ->get();

        return view('student.dashboard', compact('classes'));
    }
}
