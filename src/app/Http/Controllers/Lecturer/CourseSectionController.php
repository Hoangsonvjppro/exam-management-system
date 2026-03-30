<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseSection\StoreCourseSectionRequest;
use App\Http\Requests\CourseSection\UpdateCourseSectionRequest;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseSectionController extends Controller
{
    public function __construct(
        private readonly \App\Services\CourseSectionService $courseSectionService
    ) {
    }

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $sections = $user->courseSections()
            ->withCount('students')
            ->latest()
            ->paginate(12);

        // Load data cho slide-over form tạo lớp mới
        $subjects = \App\Models\Subject::orderBy('name')->get();
        $semesters = \App\Models\Semester::orderByDesc('start_date')->get();

        return view('lecturer.classes.index', compact('sections', 'subjects', 'semesters'));
    }

    public function create(): View
    {
        $subjects = \App\Models\Subject::orderBy('name')->get();
        $semesters = \App\Models\Semester::orderByDesc('start_date')->get();

        return view('lecturer.classes.create', compact('subjects', 'semesters'));
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
            // Sửa 'exams' thành 'examSchedules.exam' để lấy số lượng câu hỏi thông qua đề thi của ca thi
            'examSchedules.exam' => fn($q) => $q->withCount('questions'),
            'complaints.student' => fn($q) => $q->latest(),
            'attendanceSessions.records',
        ]);

        return view('lecturer.classes.show', compact('section'));
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
}
