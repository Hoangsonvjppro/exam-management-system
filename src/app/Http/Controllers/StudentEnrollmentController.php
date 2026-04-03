<?php

namespace App\Http\Controllers;

use App\Http\Requests\Enrollment\JoinClassQrRequest;
use App\Http\Requests\Enrollment\JoinClassRequest;
use App\Models\CourseSection;
use App\Models\User;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

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

        if ($result['type'] === 'success') {
            return back()->with('success', $result['message']);
        }

        return back()->with('warning', $result['message']);
    }

    public function joinClassByQr(JoinClassQrRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $inviteCode = $request->validated('invite_code');
        $result = $this->enrollmentService->joinClass($user, $inviteCode);

        if ($result['type'] === 'onboarding') {
            return redirect()->route('onboarding.show')->with('info', $result['message']);
        }

        if ($result['type'] !== 'success') {
            return redirect()->route('student.dashboard')->with('warning', $result['message']);
        }

        $section = $this->findActiveSectionByInviteCode($inviteCode);

        if (! $section) {
            return redirect()->route('student.dashboard')->with('success', $result['message']);
        }

        return redirect()->route('student.classes.show', $section)->with('success', $result['message']);
    }

    public function leaveClass(CourseSection $courseSection): RedirectResponse
    {
        /** @var User $user */
        $user = request()->user();

        $this->enrollmentService->leaveClass($courseSection, $user);

        return back()->with('success', 'Bạn đã rời khỏi lớp học phần.');
    }

    private function findActiveSectionByInviteCode(string $inviteCode): ?CourseSection
    {
        return CourseSection::query()
            ->withInviteCode(Str::upper(trim($inviteCode)))
            ->active()
            ->first();
    }
}
