<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\GradeColumn;
use App\Models\StudentGrade;
use Illuminate\Support\Collection;

class AttendanceGradeService
{
    public const COLUMN_NAME = 'Chuyên cần';
    public const DEFAULT_WEIGHT = 10.0;
    public const DEFAULT_SCORE = 10.0;
    public const ABSENT_PENALTY = 1.0;
    public const APPROVED_LEAVE_PENALTY = 0.5;

    public function ensureColumn(CourseSection $section): GradeColumn
    {
        $column = $section->gradeColumns()
            ->where('name', self::COLUMN_NAME)
            ->where(function ($query) {
                $query->whereNull('exam_schedule_id')
                    ->orWhere('is_exam_linked', false);
            })
            ->orderBy('id')
            ->first();

        if ($column) {
            return $column;
        }

        $maxOrder = (int) ($section->gradeColumns()->max('order') ?? 0);

        return $section->gradeColumns()->create([
            'name' => self::COLUMN_NAME,
            'weight' => self::DEFAULT_WEIGHT,
            'is_exam_linked' => false,
            'exam_schedule_id' => null,
            'order' => $maxOrder + 1,
        ]);
    }

    public function seedScoresForSection(CourseSection $section, ?Collection $students = null, ?int $updaterId = null): void
    {
        $column = $this->ensureColumn($section);

        $students = $students ?? $section->students()
            ->wherePivot('status', 'enrolled')
            ->get(['users.id']);

        if ($students->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($students as $student) {
            $rows[] = [
                'grade_column_id' => $column->id,
                'student_id' => $student->id,
                'score' => self::DEFAULT_SCORE,
                'note' => 'Khởi tạo điểm chuyên cần mặc định',
                'updated_by' => $updaterId ?? $section->lecturer_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        StudentGrade::query()->insertOrIgnore($rows);
    }

    public function ensureScoreForStudent(CourseSection $section, int $studentId, ?int $updaterId = null): StudentGrade
    {
        $column = $this->ensureColumn($section);

        return StudentGrade::firstOrCreate(
            [
                'grade_column_id' => $column->id,
                'student_id' => $studentId,
            ],
            [
                'score' => self::DEFAULT_SCORE,
                'note' => 'Khởi tạo điểm chuyên cần mặc định',
                'updated_by' => $updaterId ?? $section->lecturer_id,
            ]
        );
    }

    public function applyScoreDelta(CourseSection $section, int $studentId, float $delta, ?int $updaterId = null, ?string $note = null): StudentGrade
    {
        $grade = $this->ensureScoreForStudent($section, $studentId, $updaterId);

        $current = is_null($grade->score) ? self::DEFAULT_SCORE : (float) $grade->score;
        $next = round($current + $delta, 2);
        $next = min(self::DEFAULT_SCORE, max(0, $next));

        if ($next !== $current) {
            $grade->update([
                'score' => $next,
                'updated_by' => $updaterId ?? $section->lecturer_id,
                'note' => $note,
            ]);
        }

        return $grade->refresh();
    }

    public function deductScore(CourseSection $section, int $studentId, float $penalty, ?int $updaterId = null, ?string $note = null): StudentGrade
    {
        return $this->applyScoreDelta($section, $studentId, -abs($penalty), $updaterId, $note);
    }
}
