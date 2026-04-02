<?php

namespace App\Http\Controllers;

use App\Http\Requests\Onboarding\StoreOnboardingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentOnboardingController extends Controller
{
    public function show(): View
    {
        return view('onboarding');
    }

    public function store(StoreOnboardingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = auth()->user();
        $user->name = $validated['name'];
        $user->student_code = $validated['student_code'];
        $user->save();

        return redirect()->route('dashboard');
    }
}
