<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\ExamAttempt;

class StudentExamQueryService
{
    /**
     * @return array<string, mixed>
     */
    public function getIndexData(int $userId): array
    {
        $enrolledSectionIds = CourseSection::whereHas('students', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                ->where('course_section_students.status', EnrollmentService::PIVOT_ENROLLED);
        })->pluck('id');

        $exams = Exam::whereIn('course_section_id', $enrolledSectionIds)
            ->published()
            ->with('courseSection')
            ->orderBy('start_time')
            ->get();

        return [
            'upcoming' => $exams->filter(fn($exam) => ! $exam->start_time || $exam->start_time->isFuture()),
            'available' => $exams->filter(fn($exam) => $exam->start_time && $exam->start_time->isPast() && (! $exam->end_time || $exam->end_time->isFuture())),
            'ended' => $exams->filter(fn($exam) => $exam->end_time && $exam->end_time->isPast()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getShowData(Exam $exam, int $userId): array
    {
        $inProgressAttempt = ExamAttempt::forExam($exam->id)->forUser($userId)->inProgress()->first();
        $pastAttempts = ExamAttempt::forExam($exam->id)->forUser($userId)->completed()->latestAttempt()->get();

        $canStartNew = true;

        if ($exam->isOfficial() && $pastAttempts->isNotEmpty()) {
            $canStartNew = false;
        }

        if ($inProgressAttempt) {
            $canStartNew = false;
        }

        return [
            'inProgressAttempt' => $inProgressAttempt,
            'pastAttempts' => $pastAttempts,
            'canStartNew' => $canStartNew,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getRoomData(Exam $exam, ExamAttempt $attempt): array
    {
        return [
            'questions' => $exam->questions()->with('options')->get(),
            'savedAnswers' => $attempt->answers()->pluck('question_option_id', 'question_id')->toArray(),
        ];
    }

    public function getInProgressAttempt(Exam $exam, int $userId): ?ExamAttempt
    {
        return ExamAttempt::forExam($exam->id)->forUser($userId)->inProgress()->first();
    }

    public function getCompletedAttempt(Exam $exam, int $userId): ExamAttempt
    {
        return ExamAttempt::forExam($exam->id)
            ->forUser($userId)
            ->completed()
            ->latestAttempt()
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    public function getResultData(Exam $exam, ExamAttempt $attempt): array
    {
        $answers = $attempt->answers()
            ->with(['option', 'question.options'])
            ->get();

        return [
            'answers' => $answers,
            'correctCount' => $answers->where('is_correct', true)->count(),
            'totalQuestions' => $exam->questions()->count(),
            'passed' => $attempt->total_score >= $exam->pass_points,
        ];
    }
}
