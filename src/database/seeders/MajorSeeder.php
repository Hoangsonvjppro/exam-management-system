<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Major;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'CT' => [
                ['name' => 'Công nghệ thông tin',        'code' => 'CNTT'],
                ['name' => 'Kỹ thuật phần mềm',          'code' => 'KTPM'],
                ['name' => 'Hệ thống thông tin',         'code' => 'HTTT'],
                ['name' => 'An toàn thông tin',          'code' => 'ATTT'],
                ['name' => 'Trí tuệ nhân tạo',           'code' => 'AI'],
            ],
            'TN' => [
                ['name' => 'Sư phạm hóa học',           'code' => 'SPHH'],
                ['name' => 'Sư phạm vật lý',            'code' => 'SPVL'],
                ['name' => 'Sư phạm sinh học',          'code' => 'SPSH'],
            ],
        ];

        foreach ($data as $deptCode => $majors) {
            $department = Department::where('code', $deptCode)->first();
            if (! $department) continue;

            foreach ($majors as $majorData) {
                Major::firstOrCreate(
                    ['code' => $majorData['code']],
                    array_merge($majorData, [
                        'department_id' => $department->id,
                        'is_active'     => true,
                    ])
                );
            }
        }
    }
}