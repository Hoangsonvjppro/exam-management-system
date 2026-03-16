<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'code'         => ['required', 'string', 'max:50', 'unique:course_sections,code'],
            'max_students' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $section = CourseSection::create([
            'name'         => $validated['name'],
            'code'         => strtoupper($validated['code']),
            'invite_code'  => strtoupper(Str::random(6)),
            'lecturer_id'  => $user->id,
            'max_students' => $validated['max_students'] ?? 100,
            'status'       => 'active',
        ]);

        return redirect()
            ->route('lecturer.classes.show', $section)
            ->with('success', 'Tạo lớp học phần thành công. Mã tham gia: ' . $section->invite_code);
    }

    public function show(CourseSection $section): View
    {
        $this->authorizeSection($section);

        $section->load(['students' => function ($q) {
            $q->orderBy('name');
        }]);

        return view('lecturer.classes.show', compact('section'));
    }

    public function edit(CourseSection $section): View
    {
        $this->authorizeSection($section);

        return view('lecturer.classes.edit', compact('section'));
    }

    public function update(Request $request, CourseSection $section): RedirectResponse
    {
        $this->authorizeSection($section);

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'code'         => ['required', 'string', 'max:50', 'unique:course_sections,code,' . $section->id],
            'max_students' => ['nullable', 'integer', 'min:1', 'max:500'],
            'status'       => ['required', 'in:active,archived,cancelled'],
        ]);

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
        $this->authorizeSection($section);

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
        $this->authorizeSection($section);

        $section->update(['invite_code' => strtoupper(Str::random(6))]);

        return back()->with('success', 'Đã tạo mã mời mới: ' . $section->invite_code);
    }

    private function authorizeSection(CourseSection $section): void
    {
        if ($section->lecturer_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập lớp học này.');
        }
    }
}
