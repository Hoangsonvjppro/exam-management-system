<?php

namespace App\Http\Controllers;

use App\Models\CourseSection;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class StudentEnrollmentController extends Controller
{
    public function joinClass(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string'],
        ]);

        $section = CourseSection::where('invite_code', $validated['invite_code'])->firstOrFail();

        $alreadyJoined = $section->students()
            ->where('student_id', auth()->user()->id)
            ->exists();

        if (! $alreadyJoined) {
            $section->students()->attach(auth()->id(), [
                'status' => 'enrolled',
                'enrolled_at' => now(),
            ]);
        }

        return back();
    }
}
