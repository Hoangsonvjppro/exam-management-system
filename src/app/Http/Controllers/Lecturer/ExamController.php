<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\ReopenExamRequest;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Models\Chapter;
use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\Question;
use App\Services\ExamService;
use Illuminate\Support\Facades\Gate;

class ExamController extends Controller
{
    public function __construct(private readonly ExamService $examService)
    {
    }

    // Danh sách đề thi của giảng viên
    public function index()
    {
        $exams = Exam::whereHas('courseSection', function ($q) {
            $q->where('lecturer_id', \Illuminate\Support\Facades\Auth::id());
        })->with('courseSection')->latest()->paginate(20);

        return view('lecturer.exams.index', compact('exams'));
    }

    // Hiển thị form tạo đề thi mới trong 1 lớp học phần
    public function create(CourseSection $courseSection)
    {
        Gate::authorize('manage', $courseSection);

        $questions = Question::approvedForSubject($courseSection->subject_id)->get();
        $chapters = Chapter::where('subject_id', $courseSection->subject_id)
            ->orderBy('order')
            ->get();

        return view("lecturer.exams.create", compact('courseSection', 'questions', 'chapters'));
    }

    // Tạo 1 đề thi mới: phân luồng manual vs matrix
    public function store(StoreExamRequest $request, CourseSection $courseSection)
    {
        Gate::authorize('manage', $courseSection);

        $validated = $request->validated();

        try {
            if ($validated['creation_mode'] === 'matrix') {
                $exam = $this->examService->createExamFromMatrix(
                    $courseSection,
                    $validated,
                    $validated['matrix']
                );
            } else {
                $exam = $this->examService->createExam($courseSection, $validated);
            }
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('lecturer.exams.show', $exam->id)
            ->with('success', 'Đề thi đã được tạo thành công.');
    }

    // Xem chi tiết đề thi
    public function show(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $exam->load(['courseSection', 'questions', 'attempts.user']);
        $attemptCount = $exam->attempts()->count();
        $completedCount = $exam->attempts()->completed()->count();

        return view('lecturer.exams.show', compact('exam', 'attemptCount', 'completedCount'));
    }

    // Hiển thị form sửa đề thi
    public function edit(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $courseSection = $exam->courseSection;
        $questions = Question::approvedForSubject($courseSection->subject_id)->get();
        $selectedQuestionIds = $exam->questions()->pluck('question_id')->toArray();

        return view('lecturer.exams.edit', compact('exam', 'courseSection', 'questions', 'selectedQuestionIds'));
    }

    // Cập nhật đề thi
    public function update(UpdateExamRequest $request, Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $this->examService->updateExam($exam, $request->validated());

        return redirect()->route('lecturer.exams.show', $exam->id)
            ->with('success', 'Đề thi đã được cập nhật.');
    }

    // Xoá đề thi: hard-delete nếu chưa có attempt, soft-delete nếu đã có
    public function destroy(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $courseSectionId = $exam->course_section_id;
        $message = $this->examService->deleteExam($exam);

        return redirect()->route('lecturer.classes.show', $courseSectionId)
            ->with('success', $message);
    }

    // Publish đề thi (draft → published)
    public function publish(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        try {
            $this->examService->publishExam($exam);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đề thi đã được mở.');
    }

    // Đóng đề thi (published → closed)
    public function close(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        try {
            $this->examService->closeExam($exam);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đề thi đã được đóng lại.');
    }

    // Mở lại đề thi (closed → published) — yêu cầu lý do
    public function reopen(ReopenExamRequest $request, Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        try {
            $this->examService->reopenExam($exam, $request->validated()['reopen_reason']);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đề thi đã được mở lại.');
    }
}
