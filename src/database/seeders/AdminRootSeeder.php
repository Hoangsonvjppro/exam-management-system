<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminRootSeeder extends Seeder
{
    public function run(): void
    {
        $rootEmail = env('EMS_ROOT_ADMIN_EMAIL', 'root@ems.local');
        $rootPassword = env('EMS_ROOT_ADMIN_PASSWORD', 'password');

        $root = Admin::updateOrCreate(
            ['email' => $rootEmail],
            [
                'name' => 'Root Administrator',
                'password' => Hash::make($rootPassword),
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );

        $rootRole = Role::firstOrCreate([
            'name' => 'root_admin',
            'guard_name' => 'admin',
        ]);

        $root->syncRoles([$rootRole]);

        $this->command->info('✅ Root admin seeded.');
        $this->command->line("   Email: {$rootEmail}");
    }
}