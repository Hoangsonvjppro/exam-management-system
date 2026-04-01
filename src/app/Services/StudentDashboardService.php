<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\ExamSchedule;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class StudentDashboardService
{
    /**
     * Dashboard data — urgency-driven.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(User $user): array
    {
        $enrolledSections = $user->enrolledSections()->withCount('students')->with('lecturer')->get();
        $sectionIds = $enrolledSections->pluck('id');

        // All schedules (backward compat)
        $schedules = ExamSchedule::whereIn('course_section_id', $sectionIds)
            ->whereHas('exam', fn($q) => $q->published())
            ->with(['exam.subject', 'courseSection'])
            ->orderByDesc('exam_date')
            ->orderByDesc('start_time')
            ->get();

        // Upcoming exams — not yet ended, ordered by nearest first, max 6
        $upcomingExams = ExamSchedule::whereIn('course_section_id', $sectionIds)
            ->whereHas('exam', fn($q) => $q->published())
            ->where(function ($q) {
                $q->where('exam_date', '>', now()->toDateString())
                    ->orWhere(function ($q2) {
                        $q2->where('exam_date', now()->toDateString())
                            ->where('end_time', '>=', now()->toTimeString());
                    });
            })
            ->with(['exam.subject', 'courseSection'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        // Recent notifications for this user
        $recentNotifications = collect();
        if (Schema::hasTable('user_notifications')) {
            try {
                $recentNotifications = UserNotification::where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get();
            } catch (QueryException) {
                $recentNotifications = collect();
            }
        }

        return [
            'enrolledSections'     => $enrolledSections,
            'schedules'            => $schedules,
            'upcomingExams'        => $upcomingExams,
            'recentNotifications'  => $recentNotifications,
        ];
    }

    /**
     * Classes list data.
     *
     * @return array<string, mixed>
     */
    public function getClassesData(User $user): array
    {
        return [
            'enrolledSections' => $user->enrolledSections()
                ->withCount('students')
                ->with('lecturer')
                ->get(),
        ];
    }

    /**
     * Class Workspace data — 3 tabs (Feed, Exams, Grades).
     *
     * @return array<string, mixed>
     */
    public function getClassShowData(User $user, CourseSection $section): array
    {
        // Notifications/Feed for this section (for this student)
        $notifications = collect();
        if (Schema::hasTable('user_notifications')) {
            try {
                $notifications = UserNotification::where('user_id', $user->id)
                    ->where('data->course_section_id', $section->id)
                    ->orderByDesc('created_at')
                    ->limit(20)
                    ->get();
            } catch (QueryException) {
                $notifications = collect();
            }
        }

        // Exam schedules for this section
        $examSchedules = ExamSchedule::where('course_section_id', $section->id)
            ->whereHas('exam', fn($q) => $q->published())
            ->with(['exam.subject'])
            ->orderByDesc('exam_date')
            ->orderByDesc('start_time')
            ->get();

        // Determine status for each schedule for this student
        $examSchedules->each(function (ExamSchedule $schedule) use ($user) {
            $now = now();
            $examDate = Carbon::parse((string) $schedule->exam_date)->format('Y-m-d');
            $startDt = Carbon::parse($examDate . ' ' . $schedule->start_time);
            $endDt = Carbon::parse($examDate . ' ' . $schedule->end_time);

            if ($schedule->status === 'cancelled') {
                $schedule->student_status = 'cancelled';
                $schedule->student_status_label = 'Đã hủy';
            } elseif ($now->lt($startDt)) {
                $schedule->student_status = 'upcoming';
                $schedule->student_status_label = 'Sắp mở';
            } elseif ($now->between($startDt, $endDt)) {
                // Check if student has completed
                $completed = $schedule->isCompletedBy($user->id);
                if ($completed) {
                    $schedule->student_status = 'submitted';
                    $schedule->student_status_label = 'Đã nộp';
                } else {
                    $schedule->student_status = 'in_progress';
                    $schedule->student_status_label = 'Đang diễn ra';
                }
            } else {
                $completed = $schedule->isCompletedBy($user->id);
                if ($completed) {
                    $schedule->student_status = 'graded';
                    $schedule->student_status_label = 'Đã có điểm';
                } else {
                    $schedule->student_status = 'ended';
                    $schedule->student_status_label = 'Đã kết thúc';
                }
            }
        });

        // Grades — completed attempts for this student in this section's exams
        $completedAttempts = \App\Models\ExamAttempt::where('user_id', $user->id)
            ->whereHas('schedule', fn($q) => $q->where('course_section_id', $section->id))
            ->where('status', 'completed')
            ->with(['schedule.exam', 'complaint'])
            ->orderByDesc('completed_at')
            ->get();

        // Attendance
        $attendanceSessions = \App\Models\AttendanceSession::where('course_section_id', $section->id)
            ->with(['records' => function ($q) use ($user) {
                $q->where('student_id', $user->id);
            }])
            ->orderByDesc('date')
            ->get();

        // Leave Requests
        $leaveRequests = \App\Models\LeaveRequest::where('student_id', $user->id)
            ->where('course_section_id', $section->id)
            ->orderByDesc('created_at')
            ->get();

        // Eager load section with grade columns for process score
        $section->load([
            'subject',
            'lecturer',
            'semester',
            'gradeColumns.studentGrades' => function ($query) use ($user) {
                $query->where('student_id', $user->id);
            }
        ]);

        return [
            'section'            => $section,
            'notifications'      => $notifications,
            'examSchedules'      => $examSchedules,
            'completedAttempts'  => $completedAttempts,
            'attendanceSessions' => $attendanceSessions,
            'leaveRequests'      => $leaveRequests,
        ];
    }
}
