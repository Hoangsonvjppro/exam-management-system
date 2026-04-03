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
                $q2->where('student_id', $user->id)
                    ->where('course_section_students.status', EnrollmentService::PIVOT_ENROLLED);
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
            : ($semesters->first(fn(Semester $semester): bool => $semester->isCurrentPeriod()) ?? $semesters->first());

        if (!$currentSemester) {
            $currentSemester = $semesters->first();
        }

        // Lấy danh sách lớp học phần trong học kỳ đó
        $sections = CourseSection::where('semester_id', $currentSemester->id)
            ->whereHas('students', function ($q) use ($user) {
                $q->where('student_id', $user->id)
                    ->where('course_section_students.status', EnrollmentService::PIVOT_ENROLLED);
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
            $this->applyComputedScores($section);

            // Tính điểm trung bình môn học kỳ
            $credits = $section->subject->credits ?? 0;
            $totalCredits += $credits;

            // Tính gpa có trọng số (hiện tại tính tích luỹ tạm thời trên số môn đã có. 
            // Nếu muốn chỉ tính môn có đủ điểm thì bỏ check này hoặc đưa vào block if ($hasAllGrades))
            // Ở đây tính theo điểm hiện tại hiện có.
            $totalWeightedScore10 += ($section->final_score_10 * $credits);
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

    public function applyComputedScores(CourseSection $section): CourseSection
    {
        $computed = $this->computeSectionScores($section);

        $section->final_score_10 = $computed['final_score_10'];
        $section->final_score_4 = $computed['final_score_4'];
        $section->letter_grade = $computed['letter_grade'];
        $section->has_all_grades = $computed['has_all_grades'];

        return $section;
    }

    public function computeSectionScores(CourseSection $section): array
    {
        $weightedScore10 = 0;
        $hasAllGrades = true;
        $totalWeight = 0;

        foreach ($section->gradeColumns as $column) {
            $weight = (float) $column->weight;
            $totalWeight += $weight;

            $grade = $column->studentGrades->first();
            if ($grade && $grade->score !== null) {
                $weightedScore10 += ((float) $grade->score) * ($weight / 100);
            } else {
                $hasAllGrades = false;
            }
        }

        // Normalize by actual total weight and clamp to 0-10 for legacy inconsistent data.
        $normalizedScore10 = $totalWeight > 0
            ? ($weightedScore10 * 100) / $totalWeight
            : 0;
        $finalScore10 = round(max(0, min(10, $normalizedScore10)), 2);

        $conversion = $this->convertGradeTo4AndLetter($finalScore10);

        return [
            'final_score_10' => $finalScore10,
            'final_score_4' => $conversion['gpa4'],
            'letter_grade' => $conversion['letter'],
            'has_all_grades' => $hasAllGrades && $totalWeight >= 100,
        ];
    }

    /**
     * Chuyển đổi điểm hệ 10 sang hệ 4 và điểm chữ theo chuẩn VN.
     */
    public function convertGradeTo4AndLetter(float $score10): array
    {
        // 1. Kiểm tra tính hợp lệ
        // if ($score10 < 0.0 || $score10 > 10.0) {
        //     throw new InvalidArgumentException('Điểm hệ 10 phải nằm trong khoảng từ 0.0 đến 10.0');
        // }

        // 2. Tính điểm hệ 4 linh hoạt và làm tròn 2 chữ số thập phân (Ví dụ: 8.2 -> 3.28)
        $gpa4 = round($score10 * 0.4, 2);

        // 3. Xác định điểm chữ tương ứng
        if ($score10 >= 8.5) {
            $letter = 'A';
        } elseif ($score10 >= 8.0) {
            $letter = 'B+';
        } elseif ($score10 >= 7.0) {
            $letter = 'B';
        } elseif ($score10 >= 6.5) {
            $letter = 'C+';
        } elseif ($score10 >= 5.5) {
            $letter = 'C';
        } elseif ($score10 >= 5.0) {
            $letter = 'D+';
        } elseif ($score10 >= 4.0) {
            $letter = 'D';
        } else {
            $letter = 'F';
        }

        return [
            'gpa4' => $gpa4,
            'letter' => $letter
        ];
    }
}
