<?php

namespace App\Exports;

use App\Models\CourseSection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProcessGradesExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function __construct(
        private readonly CourseSection $section
    ) {}

    public function headings(): array
    {
        $columnHeadings = $this->section->gradeColumns
            ->map(fn($column) => sprintf('%s (%s%%)', $column->name, (float) $column->weight))
            ->all();

        return array_merge([
            'MSSV',
            'Ho ten',
        ], $columnHeadings, ['Tong diem QT (tam tinh)']);
    }

    public function array(): array
    {
        $rows = [];
        $gradeColumns = $this->section->gradeColumns;
        $totalWeight = (float) $gradeColumns->sum('weight');

        foreach ($this->sortedStudents() as $student) {
            $row = [
                $student->student_code,
                $student->name,
            ];

            $weightedScore = 0.0;
            $hasAnyScore = false;

            foreach ($gradeColumns as $column) {
                $grade = $column->studentGrades->firstWhere('student_id', $student->id);
                $score = $grade?->score;

                $row[] = $this->formatNullableScore($score);

                if ($score !== null && $score !== '') {
                    $weightedScore += ((float) $score) * (((float) $column->weight) / 100);
                    $hasAnyScore = true;
                }
            }

            $processScore = ($totalWeight > 0 && $hasAnyScore)
                ? round(($weightedScore * 100) / $totalWeight, 2)
                : null;

            $row[] = $this->formatNullableScore($processScore);

            $rows[] = $row;
        }

        return $rows;
    }

    private function sortedStudents(): Collection
    {
        return $this->section->students->sortBy(fn($student) => mb_strtolower((string) $student->name));
    }

    private function formatNullableScore(mixed $score): string
    {
        if ($score === null || $score === '') {
            return '';
        }

        return number_format((float) $score, 2, '.', '');
    }
}
