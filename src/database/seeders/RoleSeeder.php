<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * ============================================================
 * RoleSeeder — Tạo 5 vai trò mặc định
 * ============================================================
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'code' => 'admin',
                'name' => 'Quản trị viên',
                'description' => 'Toàn quyền hệ thống: quản lý users, cấu hình, xem báo cáo tổng quan.',
                'is_active' => true,
            ],
            [
                'code' => 'lecturer',
                'name' => 'Giảng viên',
                'description' => 'Tạo đề thi, quản lý câu hỏi, điểm danh, upload tài liệu, xem kết quả.',
                'is_active' => true,
            ],
            [
                'code' => 'student',
                'name' => 'Sinh viên',
                'description' => 'Làm bài thi, xem điểm, tải tài liệu, điểm danh.',
                'is_active' => true,
            ],
            [
                'code' => 'teaching_assistant',
                'name' => 'Trợ giảng',
                'description' => 'Hỗ trợ giảng viên: quản lý câu hỏi, điểm danh, xem kết quả lớp phụ trách.',
                'is_active' => true,
            ],
            [
                'code' => 'department_admin',
                'name' => 'Admin khoa',
                'description' => 'Quản lý môn học và giảng viên trong khoa. Xem báo cáo cấp khoa.',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['code' => $role['code']], $role);
        }

        $this->command->info('✓ Đã tạo 5 vai trò mặc định.');
    }
}
