<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Công nghệ thông tin',         'code' => 'CT'],
            ['name' => 'Sư phạm Khoa học Tự nhiên',   'code' => 'TN'],
        ];

        foreach ($departments as $data) {
            Department::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}