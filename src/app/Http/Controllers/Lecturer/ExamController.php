<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Exam;

class ExamController extends Controller
{
    // Hiển thị form tạo đề thi mới trong 1 lớp học phần
    public function create(CourseSection $courseSection)
    {
        // Kiểm tra giảng viên có sở hữu lớp này không
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
        ]);

        $exam = $courseSection->exams()->create($validated);

        return redirect()->route('lecturer.exams.questions.manage', $exam->id)
        ->with('success',
                'Đề thi đã được tạo thành công 
                || Bước tiếp theo là thêm câu hỏi vào đề thi.
            ');
    }

    // Hiển thị form quản lý câu hỏi của đề thi
    public function manageQuestions(Exam $exam)
    {
        $this->authorize('manageLecturer', $exam);

        // Lọc câu hỏi theo subject của lớp và status approved (High #8)
        $questions = Question::where('status', 'approved')
            ->where('subject_id', $exam->courseSection->subject_id)
            ->get();

        $selectedQuestionIds = $exam->questions()->pluck('question_id')->toArray();
        return view('lecturer.exams.questions', compact('exam', 'questions', 'selectedQuestionIds'));
    }

    // Thêm một method publish đề thi, để tụi sinh viên có thể vào làm bài.
    public function publish(Exam $exam)
    {
        $this->authorize('manageLecturer', $exam);

        // Kiểm tra bài thi có câu hỏi hay không? Nếu ko có thì khỏi publish
        if ($exam->questions()->count() === 0) {
            return back()->with('error','Đề kiểm tra phải có ít nhất một câu hỏi');
        }

        $exam->update(['status' => 'published']);

        return back()->with('success','Đề thi đã được mở');
    }

    // Có danh sách câu hỏi rồi thì sẽ lưu thông tin câu hỏi vào bảng trung gian exam_questions
    public function storeQuestions(Request $request, Exam $exam)
    {
        $this->authorize('manageLecturer', $exam);

        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        // Chỗ này để mặc định mỗi câu hỏi 1 điểm, 
        // Ae thấy nếu có thể tùy chỉnh điểm cho mỗi câu thì viết logic tùy chỉnh điểm sau nhé
        $questionsData = collect($request->question_ids)->mapWithKeys(function ($id, $index) {
            return [$id => ['points' => 1.00, 'order_index' => $index + 1]];
        })->all();

        $exam->questions()->sync($questionsData);

        // Đồng bộ total_points theo tổng điểm câu hỏi thực tế (Medium #16)
        $totalPoints = $exam->questions()->sum('exam_questions.points');
        $exam->update([
            'total_points' => $totalPoints,
            'pass_points' => min($exam->pass_points, $totalPoints),
        ]);
        
        return redirect()->route('lecturer.classes.show', $exam->course_section_id)
            ->with('success', 'Câu hỏi đã được cập nhật cho đề thi.');
    }
    
    public function close(Exam $exam)
    {
        $this->authorize('manageLecturer', $exam);

        $exam->update(['status' => 'closed']);
        return back()->with('success', 'Đề thi đã được đóng lại.');
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