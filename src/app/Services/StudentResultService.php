<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Support\Collection;

class StudentResultService
{
    /**
     * Lấy dữ liệu cho trang kết quả học tập.
     */
    public function getResultsData(User $user, ?int $semesterId = null): array
    {
        // Danh sách học kỳ sinh viên có học
        $semesters = Semester::whereHas('courseSections', function ($q) use ($user) {
            $q->whereHas('students', function ($q2) use ($user) {
                $q2->where('student_id', $user->id);
            });
        })->orderByDesc('start_date')->get();

        if ($semesters->isEmpty()) {
            return [
                'semesters' => collect(),
                'currentSemester' => null,
                'sections' => collect(),
                'summary' => [
                    'total_sections' => 0,
                    'total_credits' => 0,
                    'gpa_10' => 0,
                    'gpa_4' => 0,
                ]
            ];
        }

        // Chọn học kỳ
        $currentSemester = $semesterId 
            ? $semesters->firstWhere('id', $semesterId) 
            : ($semesters->firstWhere('is_current', true) ?? $semesters->first());

        if (!$currentSemester) {
            $currentSemester = $semesters->first();
        }

        // Lấy danh sách lớp học phần trong học kỳ đó
        $sections = CourseSection::where('semester_id', $currentSemester->id)
            ->whereHas('students', function ($q) use ($user) {
                $q->where('student_id', $user->id);
            })
            ->with([
                'subject',
                'lecturer',
                'gradeColumns' => function ($q) {
                    $q->orderBy('order');
                },
                'gradeColumns.studentGrades' => function ($q) use ($user) {
                    $q->where('student_id', $user->id);
                }
            ])
            ->get();

        // Tính điểm cho từng lớp
        $totalCredits = 0;
        $totalWeightedScore10 = 0;
        $totalWeightedScore4 = 0;

        foreach ($sections as $section) {
            $sectionTotal10 = 0;
            $hasAllGrades = true;
            $totalWeight = 0;

            foreach ($section->gradeColumns as $column) {
                $grade = $column->studentGrades->first();
                if ($grade && $grade->score !== null) {
                    $sectionTotal10 += ($grade->score * ($column->weight / 100));
                } else {
                    $hasAllGrades = false;
                }
                $totalWeight += $column->weight;
            }

            // Điểm tổng hệ 10
            $sectionFinal10 = round($sectionTotal10, 2);
            $conversion = $this->convertGradeTo4AndLetter($sectionFinal10);
            
            $section->final_score_10 = $sectionFinal10;
            $section->final_score_4 = $conversion['gpa4'];
            $section->letter_grade = $conversion['letter'];
            $section->has_all_grades = $hasAllGrades && $totalWeight >= 100;

            // Tính điểm trung bình môn học kỳ
            $credits = $section->subject->credits ?? 0;
            $totalCredits += $credits;

            // Tính gpa có trọng số (hiện tại tính tích luỹ tạm thời trên số môn đã có. 
            // Nếu muốn chỉ tính môn có đủ điểm thì bỏ check này hoặc đưa vào block if ($hasAllGrades))
            // Ở đây tính theo điểm hiện tại hiện có.
            $totalWeightedScore10 += ($sectionFinal10 * $credits);
            $totalWeightedScore4 += ($section->final_score_4 * $credits);
        }

        $gpa10 = $totalCredits > 0 ? round($totalWeightedScore10 / $totalCredits, 2) : 0;
        $gpa4 = $totalCredits > 0 ? round($totalWeightedScore4 / $totalCredits, 2) : 0;

        return [
            'semesters' => $semesters,
            'currentSemester' => $currentSemester,
            'sections' => $sections,
            'summary' => [
                'total_sections' => $sections->count(),
                'total_credits' => $totalCredits,
                'gpa_10' => $gpa10,
                'gpa_4' => $gpa4,
            ]
        ];
    }

    /**
     * Chuyển đổi điểm hệ 10 sang hệ 4 và điểm chữ theo chuẩn VN.
     */
    public function convertGradeTo4AndLetter(float $score10): array
    {
        if ($score10 >= 8.5) {
            return ['gpa4' => 4.0, 'letter' => 'A'];
        } elseif ($score10 >= 8.0) {
            return ['gpa4' => 3.5, 'letter' => 'B+'];
        } elseif ($score10 >= 7.0) {
            return ['gpa4' => 3.0, 'letter' => 'B'];
        } elseif ($score10 >= 6.5) {
            return ['gpa4' => 2.5, 'letter' => 'C+'];
        } elseif ($score10 >= 5.5) {
            return ['gpa4' => 2.0, 'letter' => 'C'];
        } elseif ($score10 >= 5.0) {
            return ['gpa4' => 1.5, 'letter' => 'D+'];
        } elseif ($score10 >= 4.0) {
            return ['gpa4' => 1.0, 'letter' => 'D'];
        }

        return ['gpa4' => 0.0, 'letter' => 'F'];
    }
}
