<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\ExamSchedule;
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

        $schedules = ExamSchedule::whereHas('courseSection', function($q) use ($enrolledSectionIds) {
            $q->whereIn('id', $enrolledSectionIds);
        })
            ->whereHas('exam', function($q) {
                $q->published();
            })
            ->with(['courseSection', 'exam'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        return [
            'upcoming' => $schedules->filter(function($schedule) {
                return $schedule->start_datetime->isFuture();
            }),
            'available' => $schedules->filter(function($schedule) {
                return $schedule->start_datetime->isPast() && $schedule->end_datetime->isFuture();
            }),
            'ended' => $schedules->filter(function($schedule) {
                return $schedule->end_datetime->isPast();
            }),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getShowData(ExamSchedule $schedule, int $userId): array
    {
        $exam = $schedule->exam;
        $inProgressAttempt = ExamAttempt::forSchedule($schedule->id)->forUser($userId)->inProgress()->first();
        $pastAttempts = ExamAttempt::forSchedule($schedule->id)->forUser($userId)->completed()->latestAttempt()->get();

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
    public function getRoomData(ExamSchedule $schedule, ExamAttempt $attempt): array
    {
        $exam = $schedule->exam;
        return [
            'questions' => $exam->questions()->with('options')->get(),
            'savedAnswers' => $attempt->answers()->pluck('question_option_id', 'question_id')->toArray(),
        ];
    }

    public function getInProgressAttempt(ExamSchedule $schedule, int $userId): ?ExamAttempt
    {
        return ExamAttempt::forSchedule($schedule->id)->forUser($userId)->inProgress()->first();
    }

    public function getCompletedAttempt(ExamSchedule $schedule, int $userId): ExamAttempt
    {
        return ExamAttempt::forSchedule($schedule->id)
            ->forUser($userId)
            ->completed()
            ->latestAttempt()
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    public function getResultData(ExamSchedule $schedule, ExamAttempt $attempt): array
    {
        $exam = $schedule->exam;
        $answers = $attempt->answers()
            ->with(['option', 'question.options'])
            ->get();

        return [
            'answers' => $answers,
            'correctCount' => $attempt->correct_count ?? $answers->where('is_correct', true)->count(),
            'totalQuestions' => $exam->questions()->count(),
        ];
    }
}
