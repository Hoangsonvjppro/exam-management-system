<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class StudentPageController extends Controller
{
    public function classes(): View
    {
        return view('student.classes.index');
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
