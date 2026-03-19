<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\Exam;

class ExamController extends Controller
{
    // Hiển thị form tạo đề thi mới trong 1 lớp học phần
    public function create(CourseSection $courseSection)
    {
        return view("lecturer.exams.create", compact('courseSection'));
    }

    // Tạo 1 đề thi mới xong rồi thì phải lưu thông tin chung của đề thi vào DB
    public function store(Request $request, CourseSection $courseSection)
    {
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
        $questions = Question::all();

        $selectedQuestionIds = $exam->questions()->pluck('question_id')->toArray();
        return view('lecturer.exams.questions', compact('exam', 'questions', 'selectedQuestionIds'));
    }

    // Thêm một method publish đề thi, để tụi sinh viên có thể vào làm bài.
    public function publish(Exam $exam)
    {
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
        
        return redirect()->route('lecturer.classes.show', $exam->course_section_id)
            ->with('success', 'Câu hỏi đã được cập nhật cho đề thi.');
    }
    
    public function close(Exam $exam)
    {
        $exam->update(['status' => 'closed']);
        return back()->with('success', 'Đề thi đã được đóng lại.');
    }

    /*
     hàm tùy chỉnh điểm (câu hỏi)
     {
     bốc câu hỏi đó lên và nhập điểm cho nó
     trả về câu hỏi có điểm được tùy chỉnh
     }
    */

     /*
     hàm sắp xếp câu hỏi (danh sách câu hỏi sẽ hiển thị theo thứ tự trước đó)
     {
     bốc câu hỏi đó lên và nhập vị trí mới cho nó
     trả về câu hỏi có vị trí được tùy chỉnh
     */
}