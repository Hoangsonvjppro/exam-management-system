<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\ReopenExamRequest;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Models\CourseSection;
use App\Models\Difficulty;
use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamService;
use App\Services\LecturerExamQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        $assignedSubjectIds = $user
            ->subjects()
            ->pluck('subjects.id')
            ->unique();

        $subjects = \App\Models\Subject::whereIn('id', $assignedSubjectIds)->get();
        $chapters = \App\Models\Chapter::whereIn('subject_id', $assignedSubjectIds)->orderBy('order')->get();
        $difficulties = \App\Models\Difficulty::query()->orderedForQuestionBank()->get(['code', 'name']);

        // Availability map: số câu hỏi theo chapter_id × difficulty cho tất cả subjects được phân công
        $availabilityRaw = Question::query()
            ->whereIn('subject_id', $assignedSubjectIds)
            ->selectRaw('subject_id, COALESCE(chapter_id, 0) as ch_id, difficulty, COUNT(*) as cnt')
            ->groupBy('subject_id', 'ch_id', 'difficulty')
            ->get();

        $availabilityMap = [];
        foreach ($availabilityRaw as $row) {
            $key = $row->subject_id . '|' . ($row->ch_id == 0 ? 'null' : $row->ch_id) . '|' . $row->difficulty;
            $availabilityMap[$key] = (int) $row->cnt;
        }

        return view("lecturer.exams.create", compact('subjects', 'chapters', 'difficulties', 'availabilityMap'));
    }

    // Tạo 1 đề thi mới: phân luồng manual vs matrix

    public function store(StoreExamRequest $request): RedirectResponse|JsonResponse
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
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            $exam->loadMissing('subject:id,code,name');

            return response()->json([
                'success' => true,
                'message' => 'Đề thi đã được tạo thành công.',
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'subject_id' => (int) $exam->subject_id,
                    'subject_code' => $exam->subject?->code,
                    'show_url' => route('lecturer.exams.show', $exam),
                    'preview_url' => route('lecturer.exams.quick-preview', $exam),
                    'quick_update_url' => route('lecturer.exams.quick-update', $exam),
                    'edit_url' => route('lecturer.exams.edit', $exam),
                ],
            ], 201);
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
        $difficultyLabels = Difficulty::query()
            ->orderedForQuestionBank()
            ->pluck('name', 'code')
            ->toArray();

        return view('lecturer.exams.show', compact('exam', 'attemptCount', 'completedCount', 'difficultyLabels'));
    }

    // Hiển thị form sửa đề thi
    public function edit(Exam $exam): View
    {
        Gate::authorize('manageLecturer', $exam);

        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        $assignedSubjectIds = $user
            ->subjects()
            ->pluck('subjects.id')
            ->unique();

        $allAllowedSubjectIds = $assignedSubjectIds->push($exam->subject_id)->unique();

        $subjects = \App\Models\Subject::whereIn('id', $allAllowedSubjectIds)->get();
        $chapters = \App\Models\Chapter::whereIn('subject_id', $allAllowedSubjectIds)->orderBy('order')->get();
        $difficulties = \App\Models\Difficulty::query()->orderedForQuestionBank()->get(['code', 'name']);
        $quickQuestionTypes = \App\Models\QuestionType::query()
            ->active()
            ->orderedForQuestionBank()
            ->get(['id', 'name', 'code']);

        $availabilityRaw = Question::query()
            ->whereIn('subject_id', $allAllowedSubjectIds)
            ->selectRaw('subject_id, COALESCE(chapter_id, 0) as ch_id, difficulty, COUNT(*) as cnt')
            ->groupBy('subject_id', 'ch_id', 'difficulty')
            ->get();

        $availabilityMap = [];
        foreach ($availabilityRaw as $row) {
            $key = $row->subject_id . '|' . ($row->ch_id == 0 ? 'null' : $row->ch_id) . '|' . $row->difficulty;
            $availabilityMap[$key] = (int) $row->cnt;
        }

        $questions = Question::query()->whereIn('subject_id', $allAllowedSubjectIds)->get();
        $selectedQuestionIds = $exam->questions()->pluck('question_id')->toArray();
        $matrixRows = $exam->matrices()
            ->get(['chapter_id', 'difficulty', 'question_type_id', 'question_count'])
            ->map(fn($row) => [
                'chapter_id' => $row->chapter_id,
                'difficulty' => $row->difficulty,
                'question_type_id' => $row->question_type_id,
                'question_count' => $row->question_count,
            ])
            ->values()
            ->toArray();

        $initialCreationMode = count($matrixRows) > 0 ? 'matrix' : 'manual';

        return view('lecturer.exams.edit', compact(
            'exam',
            'subjects',
            'chapters',
            'difficulties',
            'quickQuestionTypes',
            'availabilityMap',
            'questions',
            'selectedQuestionIds',
            'matrixRows',
            'initialCreationMode'
        ));
    }

    // Dữ liệu tóm tắt đề thi phục vụ popup xem nhanh trong form tạo lịch thi
    public function quickPreview(Exam $exam): JsonResponse
    {
        Gate::authorize('manageLecturer', $exam);

        $exam->load([
            'subject:id,code,name',
            'examQuestions' => fn($query) => $query
                ->with('question:id,content,difficulty')
                ->orderBy('order_index'),
        ]);

        $canEditStructure = $exam->canEditStructure();

        $questionsPreview = $exam->examQuestions
            ->take(8)
            ->map(function ($examQuestion) {
                $snapshot = $examQuestion->question_snapshot ?? [];
                $rawContent = $snapshot['content'] ?? $examQuestion->question?->content ?? '';
                $cleanContent = preg_replace('/\s+/', ' ', strip_tags((string) $rawContent));

                return [
                    'order' => (int) ($examQuestion->order_index ?? 0),
                    'content' => Str::limit(trim((string) $cleanContent), 180),
                    'difficulty' => (string) ($snapshot['difficulty'] ?? $examQuestion->question?->difficulty ?? ''),
                    'points' => (float) ($examQuestion->points ?? 1),
                ];
            })
            ->values();

        return response()->json([
            'id' => $exam->id,
            'title' => $exam->title,
            'description' => $exam->description,
            'duration_minutes' => (int) $exam->duration_minutes,
            'status' => $exam->status?->value,
            'exam_type' => $exam->exam_type?->value,
            'question_count' => $exam->examQuestions->count(),
            'total_points' => (float) ($exam->total_points ?? 0),
            'attempt_count' => $exam->attempts()->count(),
            'schedule_count' => $exam->schedules()->count(),
            'can_edit_structure' => $canEditStructure,
            'show_score_after_submit' => (bool) $exam->show_score_after_submit,
            'show_answers_after_submit' => (bool) $exam->show_answers_after_submit,
            'subject' => [
                'id' => $exam->subject?->id,
                'code' => $exam->subject?->code,
                'name' => $exam->subject?->name,
            ],
            'questions_preview' => $questionsPreview,
            'full_edit_url' => route('lecturer.exams.edit', $exam),
        ]);
    }

    // Cập nhật nhanh metadata đề thi từ popup trong form tạo lịch thi
    public function quickUpdate(Request $request, Exam $exam): JsonResponse
    {
        Gate::authorize('manageLecturer', $exam);

        $canEditStructure = $exam->canEditStructure();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => $canEditStructure
                ? 'required|integer|min:1'
                : 'nullable|integer|min:1',
        ], [
            'title.required' => 'Tên đề thi là bắt buộc.',
            'duration_minutes.required' => 'Thời lượng làm bài là bắt buộc.',
            'duration_minutes.min' => 'Thời lượng làm bài phải lớn hơn 0.',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ];

        if ($canEditStructure && array_key_exists('duration_minutes', $validated)) {
            $updateData['duration_minutes'] = (int) $validated['duration_minutes'];
        }

        $exam->update($updateData);
        $exam->loadMissing('subject:id,code,name');

        return response()->json([
            'message' => 'Đã cập nhật đề thi.',
            'warning' => $canEditStructure
                ? null
                : 'Đề thi đã có sinh viên làm bài, chỉ cập nhật được tên và mô tả.',
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'duration_minutes' => (int) $exam->duration_minutes,
                'subject_id' => $exam->subject_id,
                'subject_code' => $exam->subject?->code,
                'can_edit_structure' => $canEditStructure,
            ],
        ]);
    }

    // Cập nhật đề thi
    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        Gate::authorize('manageLecturer', $exam);

        try {
            $this->examService->updateExam($exam, $request->validated());
        } catch (\RuntimeException | \LogicException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

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
