<?php

namespace App\Http\Controllers;

use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\UserStateService;

class StudentEnrollmentController extends Controller
{
    public function __construct(private readonly UserStateService $userStateService)
    {
    }

    public function joinClass(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasRole('lecturer')) {
            return back()->with('error', 'Tài khoản giảng viên không thể tham gia lớp với vai trò sinh viên.');
        }

        // Yêu cầu xác thực MSSV trước khi join lớp học phần
        if (! $user->student_code) {
            return redirect()->route('onboarding.show')
                ->with('info', 'Vui lòng nhập MSSV và lớp trước khi tham gia lớp học phần.');
        }

        $validated = $request->validate([
            'invite_code' => ['required', 'string'],
        ]);

        $section = CourseSection::where('invite_code', $validated['invite_code'])->firstOrFail();

        $alreadyJoined = $section->students()
            ->where('student_id', $user->id)
            ->exists();

        if (! $alreadyJoined) {
            $section->students()->attach($user->id, [
                'status' => 'enrolled',
                'enrolled_at' => now(),
            ]);

            $this->userStateService->syncStudentRole($user);
        }

        return back()->with('success', 'Tham gia lớp học phần thành công.');
    }

    public function leaveClass(CourseSection $courseSection): RedirectResponse
    {
        /** @var User $user */
        $user = request()->user();

        $courseSection->students()->detach($user->id);
        $this->userStateService->syncStudentRole($user);

        return back()->with('success', 'Bạn đã rời khỏi lớp học phần.');
    }
}
