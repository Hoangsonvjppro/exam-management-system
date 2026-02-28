<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Thứ tự quan trọng:
     * 1. Roles & Permissions trước (User cần role khi assignRole)
     * 2. Admin & demo users sau
     * 3. Settings cuối
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
