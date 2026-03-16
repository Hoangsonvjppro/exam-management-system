<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed default admin and demo accounts.
     */
    public function run(): void
    {
        $lecturer = User::updateOrCreate(
            ['email' => 'lecturer@ems.local'],
            [
                'name'              => 'Trần Văn Giảng Viên',
                'password'          => Hash::make('password'),
                'lecturer_code'     => 'GV001',
                'department'        => 'Công nghệ Thông tin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
        $lecturer->syncRoles(['lecturer']);

        $student = User::updateOrCreate(
            ['email' => 'student@ems.local'],
            [
                'name'              => 'Nguyễn Văn Sinh Viên',
                'password'          => Hash::make('password'),
                'student_code'      => 'SV001',
                'class_name'        => 'CNTT2021',
                'department'        => 'Công nghệ Thông tin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
        $student->syncRoles(['student']);

        $this->command->info('✅ Demo users seeded:');
        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['lecturer@ems.local', 'password', 'lecturer'],
                ['student@ems.local',  'password', 'student'],
            ]
        );
    }
}
