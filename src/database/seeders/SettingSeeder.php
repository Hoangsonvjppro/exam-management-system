<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * ============================================================
 * SettingSeeder — Cấu hình hệ thống mặc định
 * ============================================================
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key_name' => 'app_name',                'value' => 'EMS - Examination Management System', 'description' => 'Tên hệ thống'],
            ['key_name' => 'app_short_name',          'value' => 'EMS',                                  'description' => 'Tên viết tắt'],
            ['key_name' => 'max_upload_size_mb',      'value' => '20',                                   'description' => 'Dung lượng upload tối đa (MB)'],
            ['key_name' => 'allowed_file_types',      'value' => 'pdf,docx,doc,pptx,ppt,xlsx,xls,jpg,jpeg,png,gif', 'description' => 'Các loại file được phép upload'],
            ['key_name' => 'max_absent_allowed',      'value' => '3',                                    'description' => 'Số buổi vắng tối đa trước khi cảnh báo'],
            ['key_name' => 'timezone',                'value' => 'Asia/Ho_Chi_Minh',                     'description' => 'Múi giờ hệ thống'],
            ['key_name' => 'exam_auto_submit',        'value' => '1',                                    'description' => 'Tự động nộp bài khi hết giờ (1=Bật, 0=Tắt)'],
            ['key_name' => 'attendance_geo_radius_m', 'value' => '100',                                  'description' => 'Bán kính GPS tối đa cho điểm danh (mét)'],
            ['key_name' => 'file_storage_disk',       'value' => 'local',                                'description' => 'Disk mặc định cho file uploads: local, s3, minio'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key_name' => $setting['key_name']],
                ['value' => $setting['value'], 'description' => $setting['description']]
            );
        }

        $this->command->info('✅ Settings seeded successfully.');
    }
}
