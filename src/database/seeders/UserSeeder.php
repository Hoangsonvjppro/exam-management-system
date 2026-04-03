<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\StudentClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * ============================================================
 * UserSeeder — Seed tài khoản theo domain mới
 * ============================================================
 * - Giảng viên: do admin cấp tài khoản (đăng nhập mã GV + mật khẩu)
 * - Sinh viên: đăng nhập Google, sau đó tự hoàn tất hồ sơ
 *   (MSSV + họ tên đầy đủ)
 * ============================================================
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $departmentId = Department::query()
            ->where('code', 'CT')
            ->value('id');

        $studentClasses = StudentClass::query()
            ->orderByDesc('academic_year')
            ->orderBy('class_group')
            ->get();

        $lecturerCount = $this->seedLecturers($departmentId);
        [$onboardedCount, $pendingCount] = $this->seedStudents($departmentId, $studentClasses);

        $this->command->info("✅ Đã seed {$lecturerCount} tài khoản giảng viên do admin cấp (mật khẩu tạm thời: password).");
        $this->command->info("✅ Đã seed {$onboardedCount} sinh viên đã hoàn tất onboarding Google.");
        $this->command->info("✅ Đã seed {$pendingCount} tài khoản Google mới chưa hoàn tất onboarding.");
    }

    private function seedLecturers(?int $departmentId): int
    {
        $lecturers = [
            [
                'email'         => 'sang@gmail.com',
                'name'          => 'Nguyen Thanh Sang',
                'lecturer_code' => 'GV_001',
                'phone'         => '0901000001',
                'date_of_birth' => '1985-03-15',
            ],
            [
                'email'         => 'hai.lecturer@ems.edu.vn',
                'name'          => 'Tran Van Hai',
                'lecturer_code' => 'GV_002',
                'phone'         => '0901000002',
                'date_of_birth' => '1980-07-22',
            ],
            [
                'email'         => 'ba.lecturer@ems.edu.vn',
                'name'          => 'Le Thi Ba',
                'lecturer_code' => 'GV_003',
                'phone'         => '0901000003',
                'date_of_birth' => '1988-11-10',
            ],
        ];

        foreach ($lecturers as $data) {
            $lecturer = User::updateOrCreate(
                ['lecturer_code' => $data['lecturer_code']],
                [
                    'name'              => $data['name'],
                    'email'             => $data['email'],
                    'password'          => 'password',
                    'google_id'         => null,
                    'google_avatar'     => null,
                    'student_code'      => null,
                    'lecturer_code'     => $data['lecturer_code'],
                    'student_class_id'  => null,
                    'department_id'     => $departmentId,
                    'phone'             => $data['phone'],
                    'date_of_birth'     => $data['date_of_birth'],
                    'is_active'         => true,
                    'must_change_password' => true,
                    'password_changed_at'  => null,
                    'email_verified_at' => now(),
                ]
            );

            $lecturer->syncRoles(['lecturer']);
        }

        return count($lecturers);
    }

    /**
     * @return array{int, int}
     */
    private function seedStudents(?int $departmentId, Collection $studentClasses): array
    {
        $studentNames = [
            'Nguyen Van An',
            'Tran Thi Binh',
            'Le Hoang Cuong',
            'Pham Minh Duc',
            'Hoang Thi Em',
            'Vo Quang Phuc',
            'Dang Ngoc Giau',
            'Bui Thanh Ha',
            'Ngo Dinh Khoi',
            'Duong Thi Lan',
            'Truong Quoc Minh',
            'Ly Thi Ngoc',
            'Ho Van Phong',
            'Mai Thi Quynh',
            'Ta Duc Rang',
            'Chau Minh Son',
            'Dinh Thi Trang',
            'Phan Van Uy',
            'Vu Thi Van',
            'Luu Trong Xuan',
        ];

        foreach ($studentNames as $index => $name) {
            $order = $index + 1;
            $studentCode = 'SV2026' . str_pad((string) $order, 3, '0', STR_PAD_LEFT);
            $googleId = 'google-student-2026-' . str_pad((string) $order, 3, '0', STR_PAD_LEFT);
            $classId = null;

            if ($studentClasses->isNotEmpty()) {
                $classId = $studentClasses[($order - 1) % $studentClasses->count()]->id;
            }

            $dob = Carbon::create(
                year: 2002 + (($order - 1) % 4),
                month: (($order - 1) % 12) + 1,
                day: (($order - 1) % 28) + 1,
            )->toDateString();

            $student = User::updateOrCreate(
                ['student_code' => $studentCode],
                [
                    'name'              => $name,
                    'email'             => 'sv' . str_pad((string) $order, 3, '0', STR_PAD_LEFT) . '@ems.edu.vn',
                    'password'          => null,
                    'google_id'         => $googleId,
                    'google_avatar'     => 'https://i.pravatar.cc/150?u=' . $googleId,
                    'student_code'      => $studentCode,
                    'lecturer_code'     => null,
                    'date_of_birth'     => $dob,
                    'phone'             => '0902' . str_pad((string) $order, 6, '0', STR_PAD_LEFT),
                    'is_active'         => true,
                    'must_change_password' => false,
                    'password_changed_at'  => null,
                    'email_verified_at' => now(),
                    'department_id'     => $departmentId,
                    'student_class_id'  => $classId,
                ]
            );

            $student->syncRoles(['student']);
        }

        $pendingGoogleAccounts = 5;
        for ($i = 1; $i <= $pendingGoogleAccounts; $i++) {
            $email = 'pending.sv' . str_pad((string) $i, 2, '0', STR_PAD_LEFT) . '@ems.edu.vn';
            $googleId = 'google-pending-2026-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            $student = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => 'Google User ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'password'          => null,
                    'google_id'         => $googleId,
                    'google_avatar'     => 'https://i.pravatar.cc/150?u=' . $googleId,
                    'student_code'      => null,
                    'lecturer_code'     => null,
                    'phone'             => null,
                    'date_of_birth'     => null,
                    'department_id'     => null,
                    'student_class_id'  => null,
                    'is_active'         => true,
                    'must_change_password' => false,
                    'password_changed_at'  => null,
                    'email_verified_at' => now(),
                ]
            );

            $student->syncRoles(['student']);
        }

        return [count($studentNames), $pendingGoogleAccounts];
    }
}
