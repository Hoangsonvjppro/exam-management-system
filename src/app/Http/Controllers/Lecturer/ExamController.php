<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Exam;

class ExamController extends Controller
{
    // Hiển thị form tạo đề thi mới trong 1 lớp học phần
    public function create(CourseSection $courseSection)
    {
        $this->authorizeCourseSection($courseSection);

        return view("lecturer.exams.create", compact('courseSection'));
    }

    // Tạo 1 đề thi mới xong rồi thì phải lưu thông tin chung của đề thi vào DB
    public function store(Request $request, CourseSection $courseSection)
    {
        $this->authorizeCourseSection($courseSection);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'exam_type' => 'required|in:official,practice',
            'show_score_after_submit' => 'boolean',
            'show_answers_after_submit' => 'boolean',
        ]);

        $exam = $courseSection->exams()->create($validated);

        return redirect()->route('lecturer.exams.questions.manage', $exam->id)
            ->with(
                'success',
                'Đề thi đã được tạo thành công 
                || Bước tiếp theo là thêm câu hỏi vào đề thi.
            '
            );
    }

    // Xem chi tiết đề thi
    public function show(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $exam->load(['courseSection', 'questions', 'attempts.user']);
        $attemptCount = $exam->attempts()->count();
        $completedCount = $exam->attempts()->where('status', 'completed')->count();

        return view('lecturer.exams.show', compact('exam', 'attemptCount', 'completedCount'));
    }

    // Hiển thị form sửa đề thi
    public function edit(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $courseSection = $exam->courseSection;

        return view('lecturer.exams.edit', compact('exam', 'courseSection'));
    }

    // Cập nhật đề thi
    public function update(Request $request, Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'exam_type' => 'required|in:official,practice',
            'show_score_after_submit' => 'boolean',
            'show_answers_after_submit' => 'boolean',
        ]);

        // Nếu đã có SV thi, chỉ cho sửa metadata (tên, mô tả, cấu hình hiển thị)
        if (! $exam->canEditStructure()) {
            $validated = collect($validated)->only([
                'title', 'description', 
                'show_score_after_submit', 'show_answers_after_submit',
            ])->toArray();
        }

        $exam->update($validated);

        return redirect()->route('lecturer.exams.show', $exam->id)
            ->with('success', 'Đề thi đã được cập nhật.');
    }

    // Xoá đề thi: hard-delete nếu chưa có attempt, soft-delete nếu đã có
    public function destroy(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $courseSectionId = $exam->course_section_id;

        if ($exam->attempts()->exists()) {
            $exam->delete(); // soft-delete
            return redirect()->route('lecturer.classes.show', $courseSectionId)
                ->with('success', 'Đề thi đã được lưu trữ (xoá mềm) vì đã có sinh viên thi.');
        }

        // Hard-delete
        $exam->questions()->detach();
        $exam->forceDelete();

        return redirect()->route('lecturer.classes.show', $courseSectionId)
            ->with('success', 'Đề thi đã được xoá vĩnh viễn.');
    }

    // Hiển thị form quản lý câu hỏi của đề thi
    public function manageQuestions(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $questions = Question::where('status', 'approved')
            ->where('subject_id', $exam->courseSection->subject_id)
            ->get();

        $selectedQuestionIds = $exam->questions()->pluck('question_id')->toArray();
        return view('lecturer.exams.questions', compact('exam', 'questions', 'selectedQuestionIds'));
    }

    // Publish đề thi (draft → published)
    public function publish(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        if (! $exam->canTransitionTo('published')) {
            return back()->with('error', 'Không thể mở đề thi từ trạng thái "' . $exam->status . '".');
        }

        if ($exam->questions()->count() === 0) {
            return back()->with('error', 'Đề kiểm tra phải có ít nhất một câu hỏi.');
        }

        $exam->update(['status' => 'published']);

        return back()->with('success', 'Đề thi đã được mở.');
    }

    // Đóng đề thi (published → closed)
    public function close(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        if (! $exam->canTransitionTo('closed')) {
            return back()->with('error', 'Không thể đóng đề thi từ trạng thái "' . $exam->status . '".');
        }

        $exam->update(['status' => 'closed']);
        return back()->with('success', 'Đề thi đã được đóng lại.');
    }

    // Mở lại đề thi (closed → published) — yêu cầu lý do
    public function reopen(Request $request, Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        if ($exam->status !== 'closed') {
            return back()->with('error', 'Chỉ có thể mở lại đề thi đã đóng.');
        }

        $request->validate([
            'reopen_reason' => 'required|string|max:1000',
        ]);

        $exam->update([
            'status' => 'published',
            'reopen_reason' => $request->reopen_reason,
        ]);

        return back()->with('success', 'Đề thi đã được mở lại.');
    }

    // Lưu câu hỏi vào bảng trung gian exam_questions
    public function storeQuestions(Request $request, Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        $questionsData = collect($request->question_ids)->mapWithKeys(function ($id, $index) {
            return [$id => ['points' => 1.00, 'order_index' => $index + 1]];
        })->all();

        $exam->questions()->sync($questionsData);

        // Đồng bộ total_points theo tổng điểm câu hỏi thực tế
        $totalPoints = $exam->questions()->sum('exam_questions.points');
        $exam->update([
            'total_points' => $totalPoints,
            'pass_points' => min($exam->pass_points, $totalPoints),
        ]);

        return redirect()->route('lecturer.exams.show', $exam->id)
            ->with('success', 'Câu hỏi đã được cập nhật cho đề thi.');
    }

    /**
     * Kiểm tra giảng viên có sở hữu lớp học phần không.
     */
    private function authorizeCourseSection(CourseSection $courseSection): void
    {
        if ($courseSection->lecturer_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền quản lý lớp học phần này.');
        }
    }
}
