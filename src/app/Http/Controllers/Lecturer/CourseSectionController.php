<?php

namespace App\Http\Controllers\Lecturer;

use App\Enums\ExamAttemptStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseSection\StoreCourseSectionRequest;
use App\Http\Requests\CourseSection\UpdateCourseSectionRequest;
use App\Models\CourseSection;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\EnrollmentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseSectionController extends Controller
{
    public function __construct(
        private readonly \App\Services\CourseSectionService $courseSectionService,
        private readonly EnrollmentService $enrollmentService,
    ) {}

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $sections = $user->courseSections()
            ->with(['subject', 'semester'])
            ->withCount('students')
            ->latest()
            ->paginate(12);

        // Dataset cho bộ lọc và modal tạo lớp
        $subjects = $user->subjects()->orderBy('subjects.name')->get(['subjects.id', 'subjects.code', 'subjects.name']);
        $semesters = \App\Models\Semester::orderByDesc('start_date')->get();
        $createSemesters = \App\Models\Semester::query()
            ->openForCourseSectionCreation()
            ->orderByDesc('start_date')
            ->get();

        return view('lecturer.classes.index', compact('sections', 'subjects', 'semesters', 'createSemesters'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('lecturer.classes.index', ['open_create_modal' => 1]);
    }

    public function store(StoreCourseSectionRequest $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();

        $section = $this->courseSectionService->createCourseSection($user, $validated);

        if ($request->wantsJson()) {
            $section->load(['subject', 'semester']);
            $section->loadCount('students');

            $html = view('lecturer.classes.partials._section_card', compact('section'))->render();

            return response()->json([
                'success' => true,
                'message' => 'Đã tạo lớp học phần thành công. Mã tham gia: ' . $section->invite_code,
                'html'    => $html,
            ]);
        }

        return redirect()
            ->route('lecturer.classes.show', $section)
            ->with('success', 'Tạo lớp học phần thành công. Mã lớp nội bộ: ' . $section->code . '. Mã tham gia: ' . $section->invite_code);
    }

    public function show(CourseSection $section): View
    {
        Gate::authorize('manage', $section);

        $section->load([
            'students' => fn($q) => $q->orderBy('name'),
            'examSchedules' => fn($q) => $q->with([
                'exam' => fn($examQuery) => $examQuery->withCount('questions'),
                'attempts' => fn($attemptQuery) => $attemptQuery
                    ->where('status', ExamAttemptStatus::Completed)
                    ->orderByDesc('attempt_number')
                    ->orderByDesc('id'),
                'students' => fn($studentQuery) => $studentQuery->select('users.id'),
            ]),
            'complaints' => fn($q) => $q->latest()->with([
                'student:id,name',
                'attempt:id,correct_count',
                'schedule.exam' => fn($examQuery) => $examQuery->withCount('questions'),
            ]),
            'attendanceSessions.records',
            'gradeColumns.studentGrades',
        ]);

        $examStatistics = $section->examSchedules
            ->sortByDesc('created_at')
            ->values()
            ->map(function ($schedule) {
                $latestCompletedAttempts = $schedule->attempts
                    ->unique('user_id')
                    ->values();

                $scores = $latestCompletedAttempts
                    ->pluck('total_score')
                    ->filter(fn($score) => $score !== null)
                    ->map(fn($score) => (float) $score)
                    ->values();

                $submittedCount = $scores->count();
                $assignedCount = $schedule->students->count();

                return (object) [
                    'schedule_id' => $schedule->id,
                    'exam_title' => $schedule->exam?->title ?? 'Đề thi',
                    'date_range_text' => $schedule->date_range_text,
                    'time_range_text' => $schedule->time_range_text,
                    'status' => $schedule->status,
                    'submitted_count' => $submittedCount,
                    'assigned_count' => $assignedCount,
                    'average_score' => $submittedCount > 0 ? round((float) $scores->avg(), 2) : null,
                    'highest_score' => $submittedCount > 0 ? round((float) $scores->max(), 2) : null,
                    'lowest_score' => $submittedCount > 0 ? round((float) $scores->min(), 2) : null,
                ];
            });

        $studentIds = $section->students->pluck('id');

        $announcementFeed = collect();
        if ($studentIds->isNotEmpty() && Schema::hasTable('user_notifications')) {
            // Reconstruct lecturer-sent class announcements from delivered student notifications.
            try {
                $announcementFeed = UserNotification::query()
                    ->whereIn('user_id', $studentIds)
                    ->where('data->course_section_id', $section->id)
                    ->where('type', 'course_announcement')
                    ->where('title', '!=', 'Lịch thi mới')
                    ->orderByDesc('created_at')
                    ->limit(300)
                    ->get()
                    ->unique(fn($item) => $item->title . '|' . $item->message . '|' . optional($item->created_at)->format('Y-m-d H:i:s'))
                    ->map(fn($item) => (object) [
                        'created_at' => $item->created_at,
                        'title' => $item->title,
                        'message' => $item->message,
                        'source' => 'announcement',
                    ])
                    ->values();
            } catch (QueryException) {
                $announcementFeed = collect();
            }
        }

        $subjectName = $section->subject->name ?? 'Không xác định';
        $examScheduleFeed = $section->examSchedules
            ->sortByDesc('created_at')
            ->map(fn($schedule) => (object) [
                'created_at' => $schedule->created_at,
                'title' => 'Lịch thi mới',
                'message' => 'Bạn đã tạo một lịch thi mới cho môn học ' . $subjectName . '. Thời gian: ' . $schedule->start_datetime->format('H:i d/m/Y') . ' - ' . $schedule->end_datetime->format('H:i d/m/Y'),
                'source' => 'exam_schedule',
            ])
            ->values();

        $sectionFeedItems = $announcementFeed
            ->concat($examScheduleFeed)
            ->sortByDesc(fn($item) => optional($item->created_at)->timestamp ?? 0)
            ->take(30)
            ->values();

        return view('lecturer.classes.show', compact('section', 'sectionFeedItems', 'examStatistics'));
    }

    public function edit(CourseSection $section): View
    {
        Gate::authorize('manage', $section);

        return view('lecturer.classes.edit', compact('section'));
    }

    public function update(UpdateCourseSectionRequest $request, CourseSection $section): RedirectResponse|JsonResponse
    {
        Gate::authorize('manage', $section);

        $this->courseSectionService->updateCourseSection($section, $request->validated());

        if ($request->wantsJson()) {
            $section->refresh();
            $section->load(['subject', 'semester']);
            $section->loadCount('students');

            $html = view('lecturer.classes.partials._section_card', compact('section'))->render();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật lớp học phần thành công.',
                'html'    => $html,
            ]);
        }

        return redirect()
            ->route('lecturer.classes.show', $section)
            ->with('success', 'Cập nhật lớp học phần thành công.');
    }

    public function destroy(CourseSection $section): RedirectResponse
    {
        Gate::authorize('manage', $section);

        $result = $this->courseSectionService->deleteCourseSection($section);
        if (!$result['deleted']) {
            return back()->with('error', $result['message']);
        }

        return redirect()
            ->route('lecturer.classes.index')
            ->with('success', $result['message']);
    }

    public function regenerateCode(CourseSection $section): RedirectResponse
    {
        Gate::authorize('manage', $section);

        $section = $this->courseSectionService->regenerateInviteCode($section);

        return back()->with('success', 'Đã tạo mã mời mới: ' . $section->invite_code);
    }

    public function showStudent(Request $request, CourseSection $section, User $student): JsonResponse|View
    {
        Gate::authorize('manage', $section);

        $enrollment = $section->students()
            ->where('users.id', $student->id)
            ->first();

        if (! $enrollment) {
            abort(404, 'Sinh viên không thuộc lớp học phần này.');
        }

        $enrollmentStatus = (string) ($enrollment->pivot->status ?? EnrollmentService::PIVOT_ENROLLED);

        $attempts = ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereHas('schedule', fn($query) => $query->where('course_section_id', $section->id))
            ->with([
                'schedule' => fn($scheduleQuery) => $scheduleQuery
                    ->with([
                        'exam' => fn($examQuery) => $examQuery
                            ->withCount('questions')
                            ->select(['id', 'title']),
                    ])
                    ->select(['id', 'exam_id', 'course_section_id', 'exam_date', 'start_time', 'end_time']),
            ])
            ->orderByDesc('id')
            ->get();

        $attemptRows = $attempts->map(function (ExamAttempt $attempt): array {
            $statusValue = $attempt->status instanceof ExamAttemptStatus
                ? $attempt->status->value
                : (string) $attempt->status;

            $statusLabel = match ($statusValue) {
                ExamAttemptStatus::Completed->value => 'Đã nộp',
                ExamAttemptStatus::InProgress->value => 'Đang thi',
                default => 'Không xác định',
            };

            $score = $attempt->total_score !== null ? (float) $attempt->total_score : null;
            $questionCount = $attempt->schedule?->exam?->questions_count;

            return [
                'attempt_id' => (int) $attempt->id,
                'exam_title' => $attempt->schedule?->exam?->title ?? 'Đề thi',
                'attempt_number' => (int) $attempt->attempt_number,
                'status' => $statusValue,
                'status_label' => $statusLabel,
                'score' => $score,
                'correct_count' => $attempt->correct_count !== null ? (int) $attempt->correct_count : null,
                'question_count' => $questionCount !== null ? (int) $questionCount : null,
                'started_at' => $attempt->started_at?->format('H:i d/m/Y'),
                'completed_at' => $attempt->completed_at?->format('H:i d/m/Y'),
                'schedule_time' => trim(collect([
                    $attempt->schedule?->date_range_text,
                    $attempt->schedule?->time_range_text,
                ])->filter()->implode(' · ')),
            ];
        })->values();

        $completedScores = $attemptRows
            ->where('status', ExamAttemptStatus::Completed->value)
            ->pluck('score')
            ->filter(fn($score) => $score !== null)
            ->map(fn($score) => (float) $score)
            ->values();

        $payload = [
            'success' => true,
            'student' => [
                'id' => (int) $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'student_code' => $student->student_code,
                'enrollment_status' => $enrollmentStatus,
                'enrollment_status_label' => $enrollmentStatus === EnrollmentService::PIVOT_DROPPED ? 'Đã rời lớp' : 'Đang học',
            ],
            'summary' => [
                'attempt_count' => $attemptRows->count(),
                'completed_count' => $attemptRows->where('status', ExamAttemptStatus::Completed->value)->count(),
                'average_score' => $completedScores->isEmpty() ? null : round((float) $completedScores->avg(), 2),
                'highest_score' => $completedScores->isEmpty() ? null : round((float) $completedScores->max(), 2),
            ],
            'attempts' => $attemptRows,
        ];

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        $section->loadMissing(['subject:id,code,name', 'semester:id,name']);

        return view('lecturer.classes.student-show', [
            'section' => $section,
            'studentDetail' => $payload['student'],
            'summary' => $payload['summary'],
            'attempts' => $payload['attempts'],
        ]);
    }

    public function removeStudent(Request $request, CourseSection $section, User $student): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage', $section);

        $enrollment = $section->students()
            ->where('users.id', $student->id)
            ->first();

        if (! $enrollment) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sinh viên không thuộc lớp học phần này.',
                ], 404);
            }

            return redirect()
                ->route('lecturer.classes.show', ['section' => $section, 'tab' => 'students'])
                ->with('error', 'Sinh viên không thuộc lớp học phần này.');
        }

        $enrollmentStatus = (string) ($enrollment->pivot->status ?? EnrollmentService::PIVOT_ENROLLED);
        if ($enrollmentStatus === EnrollmentService::PIVOT_DROPPED) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sinh viên này đã được xoá khỏi lớp trước đó.',
                ], 422);
            }

            return redirect()
                ->route('lecturer.classes.show', ['section' => $section, 'tab' => 'students'])
                ->with('warning', 'Sinh viên này đã được xoá khỏi lớp trước đó.');
        }

        $this->enrollmentService->leaveClass($section, $student);

        if (! ($request->expectsJson() || $request->wantsJson() || $request->ajax())) {
            return redirect()
                ->route('lecturer.classes.show', ['section' => $section, 'tab' => 'students'])
                ->with('success', 'Đã xoá sinh viên khỏi lớp học phần.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xoá sinh viên khỏi lớp học phần.',
        ]);
    }
}
