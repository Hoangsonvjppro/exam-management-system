<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class LecturerPageController extends Controller
{
    public function dashboard(): View
    {
        return view('lecturer.dashboard');
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
