<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\ReopenExamRequest;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\Question;
use App\Services\ExamService;
use App\Services\LecturerExamQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamService $examService,
        private readonly LecturerExamQueryService $lecturerExamQueryService,
    ) {}

    // Danh sách đề thi của giảng viên
    public function index(): View
    {
        $exams = $this->lecturerExamQueryService->getExamIndexForLecturer((int) \Illuminate\Support\Facades\Auth::id());

        return view('lecturer.exams.index', compact('exams'));
    }

    // Hiển thị form tạo đề thi mới
    public function create(): View
    {
        $lecturerSubjectIds = \Illuminate\Support\Facades\Auth::user()->courseSections()->pluck('subject_id')->unique();
        $subjects = \App\Models\Subject::whereIn('id', $lecturerSubjectIds)->get();
        $questions = Question::approved()->whereIn('subject_id', $lecturerSubjectIds)->get();
        $chapters = \App\Models\Chapter::whereIn('subject_id', $lecturerSubjectIds)->orderBy('order')->get();

        return view("lecturer.exams.create", compact('subjects', 'questions', 'chapters'));
    }

    // Tạo 1 đề thi mới: phân luồng manual vs matrix
    public function store(StoreExamRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = \Illuminate\Support\Facades\Auth::id();

        try {
            if ($validated['creation_mode'] === 'matrix') {
                $exam = $this->examService->createExamFromMatrix(
                    $validated,
                    $validated['matrix']
                );
            } else {
                $exam = $this->examService->createExam($validated);
            }
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('lecturer.exams.show', $exam->id)
            ->with('success', 'Đề thi đã được tạo thành công.');
    }

    // Xem chi tiết đề thi
    public function show(Exam $exam): View
    {
        Gate::authorize('manageLecturer', $exam);

        $exam->load(['subject', 'questions', 'attempts.user']);
        $attemptStats = $this->lecturerExamQueryService->getAttemptStats($exam);
        $attemptCount = $attemptStats['attemptCount'];
        $completedCount = $attemptStats['completedCount'];

        return view('lecturer.exams.show', compact('exam', 'attemptCount', 'completedCount'));
    }

    // Hiển thị form sửa đề thi
    public function edit(Exam $exam): View
    {
        Gate::authorize('manageLecturer', $exam);

        $lecturerSubjectIds = \Illuminate\Support\Facades\Auth::user()->courseSections()->pluck('subject_id')->unique();
        $allAllowedSubjectIds = $lecturerSubjectIds->push($exam->subject_id)->unique();

        $subjects = \App\Models\Subject::whereIn('id', $allAllowedSubjectIds)->get();
        $questions = Question::approved()->whereIn('subject_id', $allAllowedSubjectIds)->get();
        $selectedQuestionIds = $exam->questions()->pluck('question_id')->toArray();

        return view('lecturer.exams.edit', compact('exam', 'subjects', 'questions', 'selectedQuestionIds'));
    }

    // Cập nhật đề thi
    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        Gate::authorize('manageLecturer', $exam);

        $this->examService->updateExam($exam, $request->validated());

        return redirect()->route('lecturer.exams.show', $exam->id)
            ->with('success', 'Đề thi đã được cập nhật.');
    }

    // Xoá đề thi: hard-delete nếu chưa có attempt, soft-delete nếu đã có
    public function destroy(Exam $exam): RedirectResponse
    {
        Gate::authorize('manageLecturer', $exam);

        $message = $this->examService->deleteExam($exam);

        return redirect()->route('lecturer.exams.index')
            ->with('success', $message);
    }

    // Publish đề thi (draft → published)
    public function publish(Exam $exam): RedirectResponse
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
    public function close(Exam $exam): RedirectResponse
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
    public function reopen(ReopenExamRequest $request, Exam $exam): RedirectResponse
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
