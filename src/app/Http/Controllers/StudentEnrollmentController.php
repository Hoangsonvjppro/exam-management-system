<?php

namespace App\Http\Controllers;

use App\Http\Requests\Enrollment\JoinClassRequest;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use App\Services\EnrollmentService;

class StudentEnrollmentController extends Controller
{
    public function __construct(private readonly EnrollmentService $enrollmentService) {}

    public function joinClass(JoinClassRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->enrollmentService->joinClass($user, $request->validated('invite_code'));

        if ($result['type'] === 'onboarding') {
            return redirect()->route('onboarding.show')->with('info', $result['message']);
        }

        if ($result['type'] === 'invalid_code') {
            return back()->withErrors(['invite_code' => $result['message']])->withInput();
        }

        return back()->with($result['type'], $result['message']);
    }

    public function leaveClass(CourseSection $courseSection): RedirectResponse
    {
        /** @var User $user */
        $user = request()->user();

        $this->enrollmentService->leaveClass($courseSection, $user);

        return back()->with('success', 'Bạn đã rời khỏi lớp học phần.');
    }
}
