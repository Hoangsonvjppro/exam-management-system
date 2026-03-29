<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo 3 giảng viên
        $lecturers = [
            [
                'email' => 'Sang@gmail.com',
                'name' => 'Nguyễn Thanh Sang',
                'lecturer_code' => 'GV_SANG_001',
            ],
            [
                'email' => 'gv2@gmail.com',
                'name' => 'Trần Văn Hai',
                'lecturer_code' => 'GV_002',
            ],
            [
                'email' => 'gv3@gmail.com',
                'name' => 'Lê Thị Ba',
                'lecturer_code' => 'GV_003',
            ],
        ];

        foreach ($lecturers as $data) {
            $lecturer = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('password'),
                    'lecturer_code'     => $data['lecturer_code'],
                    'department'        => 'Khoa Công nghệ Thông tin',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );
            $lecturer->syncRoles(['lecturer']);
        }
        $this->command->info('✅ Tạo thành công 3 Giảng viên (Mật khẩu chung: password)');

        // 2. Tạo 10 sinh viên
        for ($i = 1; $i <= 10; $i++) {
            $studentCode = 'SV2026' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $student = User::updateOrCreate(
                ['email' => "sv{$i}@gmail.com"],
                [
                    'name'              => "Sinh viên {$i}",
                    'password'          => Hash::make('password'),
                    'student_code'      => $studentCode,
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );
            $student->syncRoles(['student']);
        }
        $this->command->info('✅ Tạo thành công 10 Sinh viên (Mật khẩu chung: password)');
    }
}
