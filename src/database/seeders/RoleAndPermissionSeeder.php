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

        $adminCrudModules = [
            'users',
            'admins',
            'roles',
            'announcements',
            'students',
            'student-classes',
            'course-sections',
            'semesters',
            'departments',
            'majors',
            'subjects',
            'chapters',
            'lecturers',
        ];

        $adminPermissions = [];
        foreach ($adminCrudModules as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $adminPermissions[] = "admin.{$module}.{$action}";
            }
        }

        $adminPermissions = array_merge($adminPermissions, [
            'admin.users.block',
            'admin.users.reset-password',
            'admin.admins.block',
            'admin.roles.assign',
            'admin.lecturers.block',
            'admin.lecturers.assign',
            'admin.lecturers.import',
            'admin.lecturers.edit',
        ]);

        sort($adminPermissions);

        Permission::query()
            ->where('guard_name', 'admin')
            ->whereNotIn('name', $adminPermissions)
            ->get()
            ->each(fn(Permission $permission) => $permission->delete());

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
        ]);

        $student->syncPermissions([
            'course-section.view',
            'course-section.join',
            'course-section.leave',
            'document.view',
            'assignment.view',
            'exam.view',
            'notification.view',
        ]);

        $rootAdmin->syncPermissions($adminPermissions);

        $systemAdminDeniedPermissions = [
            'admin.admins.delete',
            'admin.roles.create',
            'admin.roles.update',
            'admin.roles.delete',
        ];

        $systemAdminPermissions = array_values(array_filter(
            $adminPermissions,
            fn(string $permission): bool => ! in_array($permission, $systemAdminDeniedPermissions, true)
        ));

        $systemAdmin->syncPermissions($systemAdminPermissions);

        $this->command->info('✅ Roles & Permissions seeded successfully.');
    }
}
