<?php

namespace Database\Seeders;

use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * ExamSeeder — Tạo đề thi + câu hỏi + lịch thi + SV thi
 * ============================================================
 * Tạo 3 đề thi:
 *   1. IT001 - Official Published  (10 câu, có lịch thi tương lai)
 *   2. IT003 - Practice Published  (10 câu, có lịch thi tương lai)
 *   3. IT005 - Official Draft      (5 câu, chưa có lịch thi)
 *
 * Mỗi đề có:
 *   - exam_questions + question_snapshot
 *   - exam_schedules (cho published)
 *   - exam_schedule_students
 * ============================================================
 */
class ExamSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Lấy giảng viên ──────────────────────────────────
        $lecturers = User::whereHas('roles', fn($q) => $q->where('name', 'lecturer'))
            ->orderBy('id')->get();

        if ($lecturers->isEmpty()) {
            $this->command->warn('⚠ Không tìm thấy giảng viên. Bỏ qua ExamSeeder.');
            return;
        }

        // ─── Lấy danh sách lớp HP ────────────────────────────
        $courseSections = CourseSection::with(['subject', 'students'])->get();
        if ($courseSections->isEmpty()) {
            $this->command->warn('⚠ Không tìm thấy lớp học phần. Hãy chạy CourseSectionSeeder trước.');
            return;
        }

        // ─── Đề thi 1: IT001 - Kiểm tra giữa kỳ (Official, Published) ─
        $this->createExam(
            subjectCode: 'IT001',
            title: 'Kiểm tra Giữa kỳ - Nhập môn Lập trình C++',
            description: 'Bài kiểm tra giữa kỳ bao gồm các kiến thức chương 1-3. Thời gian: 45 phút. Không sử dụng tài liệu.',
            durationMinutes: 45,
            questionCount: 10,
            totalPoints: 10.00,
            passPoints: 5.00,
            status: 'published',
            examType: 'official',
            lecturer: $lecturers[0],           // GV Sang
            courseSections: $courseSections,
            examDate: now()->addDays(7)->format('Y-m-d'),
            startTime: '08:00:00',
            endTime: '08:45:00',
        );

        // ─── Đề thi 2: IT003 - Thực hành SQL (Practice, Published) ─
        $this->createExam(
            subjectCode: 'IT003',
            title: 'Bài tập Thực hành SQL',
            description: 'Bài tập thực hành để ôn tập kiến thức SQL. Sinh viên có thể làm nhiều lần.',
            durationMinutes: 30,
            questionCount: 10,
            totalPoints: 10.00,
            passPoints: 4.00,
            status: 'published',
            examType: 'practice',
            lecturer: $lecturers[min(2, $lecturers->count() - 1)], // GV Ba
            courseSections: $courseSections,
            examDate: now()->addDays(3)->format('Y-m-d'),
            startTime: '14:00:00',
            endTime: '14:30:00',
            showScoreAfter: true,
            showAnswersAfter: true,
        );

        // ─── Đề thi 3: IT005 - Kiểm tra cuối kỳ (Official, Draft) ─
        $this->createExam(
            subjectCode: 'IT005',
            title: 'Kiểm tra Cuối kỳ - Lập trình Web (Laravel)',
            description: 'Bài kiểm tra cuối kỳ. Đề đang soạn, chưa công bố.',
            durationMinutes: 60,
            questionCount: 5,
            totalPoints: 10.00,
            passPoints: 5.00,
            status: 'draft',
            examType: 'official',
            lecturer: $lecturers[min(1, $lecturers->count() - 1)], // GV Hai
            courseSections: $courseSections,
        );

        $this->command->info('✅ Đã tạo 3 đề thi (2 published + 1 draft) với câu hỏi, lịch thi và sinh viên.');
    }

    private function createExam(
        string $subjectCode,
        string $title,
        string $description,
        int $durationMinutes,
        int $questionCount,
        float $totalPoints,
        float $passPoints,
        string $status,
        string $examType,
        User $lecturer,
        $courseSections,
        ?string $examDate = null,
        ?string $startTime = null,
        ?string $endTime = null,
        bool $showScoreAfter = true,
        bool $showAnswersAfter = false,
    ): void {
        $subject = Subject::where('code', $subjectCode)->first();
        if (!$subject) {
            $this->command->warn("  ⚠ Không tìm thấy môn {$subjectCode}. Bỏ qua.");
            return;
        }

        // ─── Tạo đề thi ──────────────────────────────────────
        $exam = Exam::updateOrCreate(
            ['title' => $title, 'subject_id' => $subject->id],
            [
                'created_by'               => $lecturer->id,
                'description'              => $description,
                'duration_minutes'         => $durationMinutes,
                'status'                   => $status,
                'exam_type'                => $examType,
                'total_points'             => $totalPoints,
                'pass_points'              => $passPoints,
                'show_score_after_submit'   => $showScoreAfter,
                'show_answers_after_submit' => $showAnswersAfter,
                'allow_late_entrance'      => true,
                'late_entrance_limit_minutes' => 10,
                'late_entrance_behavior'   => 'fixed_end',
                'min_duration_before_submit' => 5,
            ]
        );

        // ─── Gắn câu hỏi vào đề ─────────────────────────────
        $questions = Question::where('subject_id', $subject->id)
            ->inRandomOrder()
            ->limit($questionCount)
            ->get();

        $pointsEach = round($totalPoints / max($questions->count(), 1), 2);

        foreach ($questions as $orderIdx => $question) {
            $examQuestion = ExamQuestion::updateOrCreate(
                ['exam_id' => $exam->id, 'question_id' => $question->id],
                [
                    'points'      => $pointsEach,
                    'order_index' => $orderIdx + 1,
                    'question_snapshot' => $this->buildSnapshot($question),
                ]
            );
        }

        $this->command->line("   📋 Đề: {$title} | {$questions->count()} câu | {$status}");

        // ─── Tạo lịch thi (chỉ cho published) ───────────────
        if ($status === 'published' && $examDate && $startTime && $endTime) {
            // Tìm lớp HP của môn này
            $section = $courseSections->first(fn($cs) => $cs->subject_id === $subject->id);

            if ($section) {
                $schedule = DB::table('exam_schedules')->updateOrInsert(
                    ['exam_id' => $exam->id, 'course_section_id' => $section->id],
                    [
                        'exam_date'    => $examDate,
                        'start_time'   => $startTime,
                        'end_time'     => $endTime,
                        'max_students' => $section->max_students,
                        'status'       => 'scheduled',
                        'notes'        => "Lịch thi tự động tạo bởi seeder.",
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]
                );

                // Lấy schedule vừa tạo
                $scheduleRecord = DB::table('exam_schedules')
                    ->where('exam_id', $exam->id)
                    ->where('course_section_id', $section->id)
                    ->first();

                if ($scheduleRecord) {
                    // ─── Gán tất cả SV của lớp vào lịch thi ──────
                    $enrolledStudents = $section->students()
                        ->wherePivot('status', 'enrolled')
                        ->get();

                    foreach ($enrolledStudents as $student) {
                        DB::table('exam_schedule_students')->updateOrInsert(
                            [
                                'exam_schedule_id' => $scheduleRecord->id,
                                'student_id'       => $student->id,
                            ],
                            [
                                'attendance_status' => 'pending',
                                'created_at'        => now(),
                                'updated_at'        => now(),
                            ]
                        );
                    }

                    $this->command->line("   📅 Lịch thi: {$examDate} {$startTime}-{$endTime} | {$enrolledStudents->count()} SV");
                }
            }
        }
    }

    // ─── Tạo snapshot JSON cho exam_questions ─────────────────
    private function buildSnapshot(Question $question): array
    {
        $options = QuestionOption::where('question_id', $question->id)
            ->orderBy('order')
            ->get()
            ->map(fn($opt) => [
                'id'         => $opt->id,
                'label'      => $opt->label,
                'content'    => $opt->content,
                'is_correct' => $opt->is_correct,
                'order'      => $opt->order,
            ])
            ->toArray();

        return [
            'id'               => $question->id,
            'content'          => $question->content,
            'difficulty'       => $question->difficulty,
            'question_type_id' => $question->question_type_id,
            'options'          => $options,
            'snapshot_at'      => now()->toIso8601String(),
        ];
    }
}
