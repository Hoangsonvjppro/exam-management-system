<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

/**
 * ============================================================
 * UserSeeder — Tạo 3 Giảng viên + 20 Sinh viên
 * ============================================================
 * Dữ liệu đầy đủ các trường: name, email, password,
 * lecturer_code/student_code, class_name, date_of_birth,
 * department, phone, email_verified_at.
 * ============================================================
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Tạo 3 Giảng viên ────────────────────────────
        $lecturers = [
            [
                'email'         => 'Sang@gmail.com',
                'name'          => 'Nguyễn Thanh Sang',
                'lecturer_code' => 'GV_SANG_001',
                'phone'         => '0901000001',
                'date_of_birth' => '1985-03-15',
            ],
            [
                'email'         => 'gv2@gmail.com',
                'name'          => 'Trần Văn Hai',
                'lecturer_code' => 'GV_002',
                'phone'         => '0901000002',
                'date_of_birth' => '1980-07-22',
            ],
            [
                'email'         => 'gv3@gmail.com',
                'name'          => 'Lê Thị Ba',
                'lecturer_code' => 'GV_003',
                'phone'         => '0901000003',
                'date_of_birth' => '1988-11-10',
            ],
        ];

        foreach ($lecturers as $data) {
            $dob = Carbon::parse($data['date_of_birth']);
            $password = $dob->format('dmY'); // e.g. 15031985

            $lecturer = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => $password,
                    'lecturer_code'     => $data['lecturer_code'],
                    'phone'             => $data['phone'],
                    'date_of_birth'     => $data['date_of_birth'],
                    'department'        => 'Khoa Công nghệ Thông tin',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );
            $lecturer->syncRoles(['lecturer']);
        }
        $this->command->info('✅ Tạo thành công 3 Giảng viên (Mật khẩu = ngày sinh ddMMyyyy)');

        // ─── 2. Tạo 20 Sinh viên ────────────────────────────
        $studentNames = [
            'Nguyễn Văn An',    'Trần Thị Bình',   'Lê Hoàng Cường',
            'Phạm Minh Đức',    'Hoàng Thị Em',    'Võ Quang Phúc',
            'Đặng Ngọc Giàu',   'Bùi Thanh Hà',    'Ngô Đình Khôi',
            'Dương Thị Lan',    'Trương Quốc Minh', 'Lý Thị Ngọc',
            'Hồ Văn Phong',     'Mai Thị Quỳnh',   'Tạ Đức Rạng',
            'Châu Minh Sơn',    'Đinh Thị Trang',   'Phan Văn Uy',
            'Vũ Thị Vân',       'Lưu Trọng Xuân',
        ];

        $classes = ['CNTT-K20A', 'CNTT-K20B', 'CNTT-K21A', 'CNTT-K21B'];

        for ($i = 1; $i <= 20; $i++) {
            $studentCode = 'SV2026' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $dobString = '200' . rand(0, 4) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
            $dob = Carbon::parse($dobString);
            $password = $dob->format('dmY'); // e.g. 05062003

            $student = User::updateOrCreate(
                ['email' => "sv{$i}@gmail.com"],
                [
                    'name'              => $studentNames[$i - 1],
                    'password'          => $password,
                    'student_code'      => $studentCode,
                    'class_name'        => $classes[($i - 1) % count($classes)],
                    'date_of_birth'     => $dobString,
                    'department'        => 'Khoa Công nghệ Thông tin',
                    'phone'             => '090200' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );
            $student->syncRoles(['student']);
        }
        $this->command->info('✅ Tạo thành công 20 Sinh viên (Mật khẩu = ngày sinh ddMMyyyy)');
    }
}
