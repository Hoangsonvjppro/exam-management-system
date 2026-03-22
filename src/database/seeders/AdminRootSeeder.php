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
        $rootAdmin = Admin::updateOrCreate(
            ['email' => 'admin@root.com'],
            [
                'name' => 'System Root Admin',
                'password' => Hash::make('password'),
                'must_change_password' => false,
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );

        $rootRole = Role::firstOrCreate(['name' => 'root_admin', 'guard_name' => 'admin']);
        $rootAdmin->syncRoles([$rootRole]);

        $this->command->info('✅ Tạo thành công Root Admin: admin@root.com / password');
    }
}
