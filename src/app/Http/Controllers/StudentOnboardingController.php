<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentOnboardingController extends Controller
{
    public function show(): View
    {
        return view('onboarding');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_code' => ['required', 'string', 'max:20', 'unique:users,student_code'],
            'class_name' => ['required', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $user->student_code = $validated['student_code'];
        $user->class_name = $validated['class_name'];
        $user->save();

        return redirect()->route('dashboard');
    }
}
