<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('course_sections') || !Schema::hasTable('grade_columns') || !Schema::hasTable('student_grades') || !Schema::hasTable('course_section_students')) {
            return;
        }

        $now = now();

        DB::table('course_sections')
            ->select('id', 'lecturer_id')
            ->orderBy('id')
            ->chunkById(100, function ($sections) use ($now) {
                foreach ($sections as $section) {
                    $column = DB::table('grade_columns')
                        ->where('course_section_id', $section->id)
                        ->where('name', 'Chuyên cần')
                        ->where(function ($query) {
                            $query->whereNull('exam_schedule_id')
                                ->orWhere('is_exam_linked', false);
                        })
                        ->orderBy('id')
                        ->first();

                    if ($column) {
                        $columnId = $column->id;
                    } else {
                        $maxOrder = (int) (DB::table('grade_columns')->where('course_section_id', $section->id)->max('order') ?? 0);

                        $columnId = DB::table('grade_columns')->insertGetId([
                            'course_section_id' => $section->id,
                            'name' => 'Chuyên cần',
                            'weight' => 10,
                            'is_exam_linked' => false,
                            'exam_schedule_id' => null,
                            'order' => $maxOrder + 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $studentIds = DB::table('course_section_students')
                        ->where('course_section_id', $section->id)
                        ->where('status', 'enrolled')
                        ->pluck('student_id');

                    if ($studentIds->isEmpty()) {
                        continue;
                    }

                    $rows = [];
                    foreach ($studentIds as $studentId) {
                        $rows[] = [
                            'grade_column_id' => $columnId,
                            'student_id' => $studentId,
                            'score' => 10,
                            'note' => 'Khởi tạo điểm chuyên cần mặc định',
                            'updated_by' => $section->lecturer_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    DB::table('student_grades')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        // No-op: avoid accidental deletion of lecturer-managed grade data.
    }
};
