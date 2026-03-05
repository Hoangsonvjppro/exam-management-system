<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ============================================================
 * UserSeeder — Tạo users mẫu với dữ liệu tiếng Việt thực tế
 * ============================================================
 * Tạo:
 *   - 1 Admin
 *   - 5 Giảng viên (GV001 → GV005)
 *   - 30 Sinh viên (20240001 → 20240030)
 * Tổng: 36 users
 * Password mặc định: "password"
 * ============================================================
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // ─── 1. ADMIN ────────────────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@ems.local'],
            [
                'name' => 'Nguyễn Văn Admin',
                'password' => $password,
                'phone' => '0901000001',
                'department' => 'Phòng Đào tạo',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $this->assignRole($admin, 'admin');

        // ─── 2. GIẢNG VIÊN ──────────────────────────────────
        $lecturers = [
            ['name' => 'Trần Minh Tuấn', 'email' => 'tuan.tm@ems.local', 'code' => 'GV001', 'department' => 'Khoa CNTT', 'phone' => '0901100001'],
            ['name' => 'Lê Thị Hồng Nhung', 'email' => 'nhung.lth@ems.local', 'code' => 'GV002', 'department' => 'Khoa CNTT', 'phone' => '0901100002'],
            ['name' => 'Phạm Đức Hùng', 'email' => 'hung.pd@ems.local', 'code' => 'GV003', 'department' => 'Khoa CNTT', 'phone' => '0901100003'],
            ['name' => 'Nguyễn Hoàng Sơn', 'email' => 'son.nh@ems.local', 'code' => 'GV004', 'department' => 'Khoa Toán-Tin', 'phone' => '0901100004'],
            ['name' => 'Vũ Thanh Hải', 'email' => 'hai.vt@ems.local', 'code' => 'GV005', 'department' => 'Khoa CNTT', 'phone' => '0901100005'],
        ];

        foreach ($lecturers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                    'phone' => $data['phone'],
                    'lecturer_code' => $data['code'],
                    'department' => $data['department'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $this->assignRole($user, 'lecturer');
        }

        // ─── 3. SINH VIÊN ───────────────────────────────────
        $studentNames = [
            // Lớp CNTT-K48-01 (10 SV)
            ['Hoàng Anh Tuấn', 'CNTT-K48-01'],
            ['Nguyễn Thị Mai Anh', 'CNTT-K48-01'],
            ['Trần Văn Bình', 'CNTT-K48-01'],
            ['Lê Hoàng Phúc', 'CNTT-K48-01'],
            ['Phạm Thị Thanh Hà', 'CNTT-K48-01'],
            ['Đỗ Minh Quân', 'CNTT-K48-01'],
            ['Bùi Thị Ngọc Ánh', 'CNTT-K48-01'],
            ['Vương Đức Thắng', 'CNTT-K48-01'],
            ['Ngô Thị Hương Giang', 'CNTT-K48-01'],
            ['Đinh Công Thành', 'CNTT-K48-01'],

            // Lớp CNTT-K48-02 (10 SV)
            ['Trịnh Quốc Đạt', 'CNTT-K48-02'],
            ['Lý Thị Phương Linh', 'CNTT-K48-02'],
            ['Cao Văn Hưng', 'CNTT-K48-02'],
            ['Nguyễn Thị Thuỷ', 'CNTT-K48-02'],
            ['Phan Minh Đức', 'CNTT-K48-02'],
            ['Tô Thị Lan Anh', 'CNTT-K48-02'],
            ['Dương Quang Huy', 'CNTT-K48-02'],
            ['Hà Thị Bảo Trân', 'CNTT-K48-02'],
            ['Lưu Hoàng Long', 'CNTT-K48-02'],
            ['Mai Thị Kim Oanh', 'CNTT-K48-02'],

            // Lớp CNTT-K48-03 (10 SV)
            ['Trương Văn Khôi', 'CNTT-K48-03'],
            ['Nguyễn Thị Diệu Linh', 'CNTT-K48-03'],
            ['Huỳnh Tấn Phát', 'CNTT-K48-03'],
            ['Lê Thị Mỹ Duyên', 'CNTT-K48-03'],
            ['Phan Quốc Bảo', 'CNTT-K48-03'],
            ['Đặng Thị Hồng Nhung', 'CNTT-K48-03'],
            ['Võ Minh Trí', 'CNTT-K48-03'],
            ['Trần Thị Thu Hà', 'CNTT-K48-03'],
            ['Nguyễn Tấn Dũng', 'CNTT-K48-03'],
            ['Lâm Thị Bích Ngọc', 'CNTT-K48-03'],
        ];

        foreach ($studentNames as $index => $data) {
            $studentCode = '2024' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $emailPrefix = $this->generateEmailPrefix($data[0], $studentCode);

            $user = User::updateOrCreate(
                ['student_code' => $studentCode],
                [
                    'name' => $data[0],
                    'email' => "{$emailPrefix}@ems.local",
                    'password' => $password,
                    'phone' => '090200' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'student_code' => $studentCode,
                    'class_name' => $data[1],
                    'department' => 'Khoa CNTT',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $this->assignRole($user, 'student');
        }

        $this->command->info('✓ Đã tạo 36 users (1 Admin, 5 GV, 30 SV).');
    }

    /**
     * Gán role cho user nếu chưa có.
     */
    private function assignRole(User $user, string $roleCode): void
    {
        $role = Role::where('code', $roleCode)->first();
        if ($role && !$user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->attach($role->id, ['assigned_at' => now()]);
        }
    }

    /**
     * Tạo email prefix từ tên (bỏ dấu + viết tắt).
     */
    private function generateEmailPrefix(string $name, string $code): string
    {
        // Lấy MSSV làm prefix cho đơn giản và unique
        return strtolower($code);
    }
}
