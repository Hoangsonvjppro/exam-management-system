<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Seed roles and base permissions.
     *
     * Roles: admin, lecturer, student, teaching_assistant, department_admin
     */
    public function run(): void
    {
        // Reset cache trước khi seed
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Permissions ──────────────────────────────────────────
        $permissions = [
            // User management
            'user.view', 'user.create', 'user.edit', 'user.delete', 'user.toggle-active',

            // Semester
            'semester.view', 'semester.create', 'semester.edit', 'semester.delete',

            // Subject & Chapter
            'subject.view', 'subject.create', 'subject.edit', 'subject.delete',
            'chapter.view', 'chapter.create', 'chapter.edit', 'chapter.delete',

            // Course section
            'course-section.view', 'course-section.create', 'course-section.edit', 'course-section.delete',
            'course-section.enroll-student',

            // Question bank
            'question.view', 'question.create', 'question.edit', 'question.delete', 'question.approve',

            // Exam
            'exam.view', 'exam.create', 'exam.edit', 'exam.delete', 'exam.publish', 'exam.schedule',

            // Attendance
            'attendance.view', 'attendance.create', 'attendance.edit',

            // Document
            'document.view', 'document.create', 'document.delete',

            // Notification
            'notification.view',

            // Settings (admin only)
            'setting.view', 'setting.edit',

            // Reports
            'report.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ─── Roles ────────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $lecturer = Role::firstOrCreate(['name' => 'lecturer', 'guard_name' => 'web']);
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $ta = Role::firstOrCreate(['name' => 'teaching_assistant', 'guard_name' => 'web']);
        $deptAdmin = Role::firstOrCreate(['name' => 'department_admin', 'guard_name' => 'web']);

        // Admin: tất cả quyền
        $admin->syncPermissions(Permission::all());

        // Lecturer
        $lecturer->syncPermissions([
            'semester.view',
            'subject.view', 'chapter.view',
            'course-section.view', 'course-section.enroll-student',
            'question.view', 'question.create', 'question.edit', 'question.delete',
            'exam.view', 'exam.create', 'exam.edit', 'exam.publish', 'exam.schedule',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'document.view', 'document.create', 'document.delete',
            'notification.view',
            'report.view',
        ]);

        // Department Admin
        $deptAdmin->syncPermissions([
            'user.view',
            'semester.view', 'semester.create', 'semester.edit',
            'subject.view', 'subject.create', 'subject.edit', 'subject.delete',
            'chapter.view', 'chapter.create', 'chapter.edit', 'chapter.delete',
            'course-section.view', 'course-section.create', 'course-section.edit',
            'question.view', 'question.approve',
            'exam.view',
            'report.view',
            'notification.view',
        ]);

        // Teaching Assistant
        $ta->syncPermissions([
            'semester.view', 'subject.view', 'chapter.view',
            'course-section.view',
            'question.view', 'question.create', 'question.edit',
            'exam.view',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'document.view', 'document.create',
            'notification.view',
        ]);

        // Student: quyền tối thiểu
        $student->syncPermissions([
            'semester.view', 'subject.view', 'chapter.view',
            'course-section.view',
            'exam.view',
            'attendance.view',
            'document.view',
            'notification.view',
        ]);

        $this->command->info('✅ Roles & Permissions seeded successfully.');
    }
}
