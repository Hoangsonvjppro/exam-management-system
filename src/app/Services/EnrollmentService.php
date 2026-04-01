<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EnrollmentService
{
    public const PIVOT_ENROLLED = 'enrolled';
    public const PIVOT_DROPPED = 'dropped';

    public function __construct(
        private readonly UserStateService $userStateService,
        private readonly AttendanceGradeService $attendanceGradeService
    ) {}

    /**
     * @return array{type:string,message:string}
     */
    public function joinClass(User $user, string $inviteCode): array
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
            return [
                'type' => 'error',
                'message' => 'He thong phan quyen chua san sang. Vui long thu lai sau.',
            ];
        }

        try {
            if ($user->hasRole('lecturer')) {
                return [
                    'type' => 'error',
                    'message' => 'Tài khoản giảng viên không thể tham gia lớp với vai trò sinh viên.',
                ];
            }
        } catch (QueryException) {
            return [
                'type' => 'error',
                'message' => 'He thong phan quyen gap loi du lieu. Vui long thu lai sau.',
            ];
        }

        if (! $user->student_code) {
            return [
                'type' => 'onboarding',
                'message' => 'Vui lòng nhập MSSV và lớp trước khi tham gia lớp học phần.',
            ];
        }

        $normalizedCode = Str::upper(trim($inviteCode));

        $section = CourseSection::query()
            ->withInviteCode($normalizedCode)
            ->active()
            ->first();

        if (! $section) {
            return [
                'type' => 'invalid_code',
                'message' => 'Ma lop khong hop le hoac lop dang dong.',
            ];
        }

        $isFull = $section->students()
            ->wherePivot('status', self::PIVOT_ENROLLED)
            ->count() >= (int) $section->max_students;

        if ($isFull) {
            return [
                'type' => 'invalid_code',
                'message' => 'Lop hoc da du so luong sinh vien.',
            ];
        }

        $existingPivot = $section->students()
            ->where('student_id', $user->id)
            ->first();

        if ($existingPivot) {
            if ($existingPivot->pivot->status === self::PIVOT_DROPPED) {
                $section->students()->updateExistingPivot($user->id, [
                    'status' => self::PIVOT_ENROLLED,
                    'enrolled_at' => now(),
                ]);

                $this->attendanceGradeService->ensureScoreForStudent($section, $user->id, $section->lecturer_id);
                $this->userStateService->syncStudentRole($user);

                return [
                    'type' => 'success',
                    'message' => 'Bạn đã tham gia lại lớp học phần thành công.',
                ];
            }

            return [
                'type' => 'success',
                'message' => 'Ban da tham gia lop hoc phan nay roi.',
            ];
        }

        $section->students()->attach($user->id, [
            'status' => self::PIVOT_ENROLLED,
            'enrolled_at' => now(),
        ]);

        $this->attendanceGradeService->ensureScoreForStudent($section, $user->id, $section->lecturer_id);

        $this->userStateService->syncStudentRole($user);

        return [
            'type' => 'success',
            'message' => 'Tham gia lop hoc phan thanh cong.',
        ];
    }

    public function leaveClass(CourseSection $courseSection, User $user): void
    {
        $courseSection->students()->updateExistingPivot($user->id, [
            'status' => self::PIVOT_DROPPED,
        ]);

        $this->userStateService->syncStudentRole($user);
    }
}
