<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $lecturer = User::updateOrCreate(
            ['email' => 'Sang@gmail.com'],
            [
                'name'              => 'Nguyễn Thanh Sang',
                'password'          => Hash::make('password'),
                'lecturer_code'     => 'GV_SANG_001',
                'department'        => 'Khoa Công nghệ Thông tin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
        $lecturer->syncRoles(['lecturer']);

        $this->command->info('✅ Tạo thành công Giảng viên: Sang@gmail.com / password');
    }
}
