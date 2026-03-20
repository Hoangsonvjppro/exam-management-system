<?php

namespace App\Http\Controllers;

use App\Http\Requests\Enrollment\JoinClassRequest;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use App\Services\UserStateService;
use Illuminate\Support\Str;

class StudentEnrollmentController extends Controller
{
    public function __construct(private readonly UserStateService $userStateService)
    {
    }

    public function joinClass(JoinClassRequest $request): RedirectResponse
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

        $inviteCode = Str::upper(trim($request->validated()['invite_code']));

        $section = CourseSection::query()
            ->withInviteCode($inviteCode)
            ->active()
            ->first();

        if (! $section) {
            return back()->withErrors([
                'invite_code' => 'Ma lop khong hop le hoac lop dang dong.',
            ])->withInput();
        }

        // Chỉ tính sinh viên enrolled khi kiểm tra sĩ số (Medium #15)
        $isFull = $section->students()
            ->wherePivot('status', 'enrolled')
            ->count() >= (int) $section->max_students;

        if ($isFull) {
            return back()->withErrors([
                'invite_code' => 'Lop hoc da du so luong sinh vien.',
            ])->withInput();
        }

        // Kiểm tra đã từng tham gia chưa
        $existingPivot = $section->students()
            ->where('student_id', $user->id)
            ->first();

        if ($existingPivot) {
            if ($existingPivot->pivot->status === 'dropped') {
                // Re-join từ trạng thái dropped (Medium #15)
                $section->students()->updateExistingPivot($user->id, [
                    'status' => 'enrolled',
                    'enrolled_at' => now(),
                ]);
                $this->userStateService->syncStudentRole($user);
                return back()->with('success', 'Bạn đã tham gia lại lớp học phần thành công.');
            }
            return back()->with('success', 'Ban da tham gia lop hoc phan nay roi.');
        }

        $section->students()->attach($user->id, [
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        $this->userStateService->syncStudentRole($user);

        return back()->with('success', 'Tham gia lop hoc phan thanh cong.');
    }

    public function leaveClass(CourseSection $courseSection): RedirectResponse
    {
        /** @var User $user */
        $user = request()->user();

        // Chuyển sang dropped thay vì detach để giữ lịch sử (Medium #15)
        $courseSection->students()->updateExistingPivot($user->id, [
            'status' => 'dropped',
        ]);
        $this->userStateService->syncStudentRole($user);

        return back()->with('success', 'Bạn đã rời khỏi lớp học phần.');
    }
}
