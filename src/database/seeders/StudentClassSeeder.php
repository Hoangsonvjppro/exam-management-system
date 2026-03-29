<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\StudentClass;
use Illuminate\Database\Seeder;

class StudentClassSeeder extends Seeder
{
    public function run(): void
    {
        $codeMap = [
            'CNTT' => [
                2023 => [
                    1 => 'DCT1231',
                    2 => 'DCT1232',
                    3 => 'DCT1233',
                ],
                2024 => [
                    1 => 'DCT1241',
                    2 => 'DCT1242',
                    3 => 'DCT1243',
                ],
            ],
            'KTPM' => [
                2023 => [
                    1 => 'DKP1231',
                    2 => 'DKP1232',
                    3 => 'DKP1233',
                ],
                2024 => [
                    1 => 'DKP1241',
                    2 => 'DKP1242',
                    3 => 'DKP1243',
                ],
            ],
            'SPVL' => [
                2023 => [
                    1 => 'DLI1231',
                    2 => 'DLI1232',
                    3 => 'DLI1233',
                ],
                2024 => [
                    1 => 'DLI1241',
                    2 => 'DLI1242',
                    3 => 'DLI1243',
                ],
            ],
        ];

        foreach ($codeMap as $majorCode => $years) {
            $major = Major::where('code', $majorCode)->first();
            if (! $major) continue;

            foreach ($years as $year => $groups) {
                foreach ($groups as $group => $code) {
                    StudentClass::firstOrCreate(
                        [
                            'major_id'      => $major->id,
                            'academic_year' => $year,
                            'class_group'   => $group,
                        ],
                        [
                            'code'      => $code,
                            'is_active' => true,
                            'name' => sprintf(
                                '%s - K.%s - Lớp %02d',
                                $major->name,
                                substr($year, -2),
                                $group
                            )
                        ]
                    );
                }
            }
        }
    }
}