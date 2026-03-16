<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $webPermissions = [
            'course-section.view',
            'course-section.join',
            'course-section.leave',
            'document.view',
            'document.create',
            'document.update',
            'document.delete',
            'assignment.view',
            'assignment.create',
            'assignment.update',
            'assignment.delete',
            'exam.view',
            'exam.create',
            'exam.update',
            'exam.delete',
            'notification.view',
        ];

        $adminPermissions = [
            'admin.users.view',
            'admin.users.create',
            'admin.users.update',
            'admin.users.block',
            'admin.users.reset-password',
            'admin.admins.view',
            'admin.admins.create',
            'admin.admins.update',
            'admin.settings.view',
            'admin.settings.update',
            'admin.reports.view',
        ];

        foreach ($webPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach ($adminPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        $lecturer = Role::firstOrCreate(['name' => 'lecturer', 'guard_name' => 'web']);
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $rootAdmin = Role::firstOrCreate(['name' => 'root_admin', 'guard_name' => 'admin']);
        $systemAdmin = Role::firstOrCreate(['name' => 'system_admin', 'guard_name' => 'admin']);

        $lecturer->syncPermissions([
            'course-section.view',
            'document.view', 'document.create', 'document.update', 'document.delete',
            'assignment.view', 'assignment.create', 'assignment.update', 'assignment.delete',
            'exam.view', 'exam.create', 'exam.update', 'exam.delete',
            'notification.view',
        ]);

        $student->syncPermissions([
            'course-section.view', 'course-section.join', 'course-section.leave',
            'document.view',
            'assignment.view',
            'exam.view',
            'notification.view',
        ]);

        $rootAdmin->syncPermissions(Permission::query()->where('guard_name', 'admin')->get());
        $systemAdmin->syncPermissions([
            'admin.users.view',
            'admin.users.create',
            'admin.users.update',
            'admin.users.block',
            'admin.users.reset-password',
            'admin.reports.view',
        ]);

        $this->command->info('✅ Roles & Permissions seeded successfully.');
    }
}
