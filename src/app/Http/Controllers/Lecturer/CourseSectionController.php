<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseSection\StoreCourseSectionRequest;
use App\Http\Requests\CourseSection\UpdateCourseSectionRequest;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseSectionController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $sections = $user->courseSections()
            ->withCount('students')
            ->latest()
            ->paginate(12);

        return view('lecturer.classes.index', compact('sections'));
    }

    public function create(): View
    {
        return view('lecturer.classes.create');
    }

    public function store(StoreCourseSectionRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $section = CourseSection::create([
            'name'         => $request->validated()['name'],
            'code'         => strtoupper($request->validated()['code']),
            'invite_code'  => strtoupper(Str::random(6)),
            'lecturer_id'  => $user->id,
            'max_students' => $request->validated()['max_students'] ?? 100,
            'status'       => 'active',
        ]);

        return redirect()
            ->route('lecturer.classes.show', $section)
            ->with('success', 'Tạo lớp học phần thành công. Mã tham gia: ' . $section->invite_code);
    }

    public function show(CourseSection $section): View
    {
        Gate::authorize('manage', $section);

        $section->load([
            'students' => fn($q) => $q->orderBy('name'),
            'exams'    => fn($q) => $q->withCount('questions'),
        ]);

        return view('lecturer.classes.show', compact('section'));
    }

    public function edit(CourseSection $section): View
    {
        Gate::authorize('manage', $section);

        return view('lecturer.classes.edit', compact('section'));
    }

    public function update(UpdateCourseSectionRequest $request, CourseSection $section): RedirectResponse
    {
        Gate::authorize('manage', $section);

        $validated = $request->validated();

        $section->update([
            'name'         => $validated['name'],
            'code'         => strtoupper($validated['code']),
            'max_students' => $validated['max_students'] ?? $section->max_students,
            'status'       => $validated['status'],
        ]);

        return redirect()
            ->route('lecturer.classes.show', $section)
            ->with('success', 'Cập nhật lớp học phần thành công.');
    }

    public function destroy(CourseSection $section): RedirectResponse
    {
        Gate::authorize('manage', $section);

        // Only allow deletion if no students are enrolled
        if ($section->students()->exists()) {
            return back()->with('error', 'Không thể xoá lớp có sinh viên đang theo học. Hãy đổi trạng thái sang "Huỷ".');
        }

        $section->delete();

        return redirect()
            ->route('lecturer.classes.index')
            ->with('success', 'Đã xoá lớp học phần.');
    }

    public function regenerateCode(CourseSection $section): RedirectResponse
    {
        Gate::authorize('manage', $section);

        $section->update(['invite_code' => strtoupper(Str::random(6))]);

        return back()->with('success', 'Đã tạo mã mời mới: ' . $section->invite_code);
    }
}
