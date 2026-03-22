<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed default admin and demo accounts.
     */
    public function run(): void
    {
        $lecturer = User::updateOrCreate(
            ['email' => 'Sang@gmail.com'],
            [
                'name'              => 'Nguyễn Thanh Sang',
                'password'          => Hash::make('password'),
                'lecturer_code'     => 'GV001',
                'department'        => 'Công nghệ Thông tin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
        $lecturer->syncRoles(['lecturer']);

        $this->command->info('✅ Demo users seeded:');
        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['lecturer@ems.local', 'password', 'lecturer'],
            ]
        );
    }
}
