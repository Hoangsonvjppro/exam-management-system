<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\IndexQuestionRequest;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\User;
use App\Services\QuestionBankQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    /**
     * API for AJAX: Get chapters by subject ID.
     */
    public function getChaptersBySubject(int $subjectId): JsonResponse
    {
        $user = Auth::user();
        if ($user instanceof User && $user->hasRole('lecturer')) {
            $isAssigned = $user->subjects()
                ->where('subjects.id', $subjectId)
                ->exists();

            if (! $isAssigned) {
                return response()->json([
                    'message' => 'Bạn không có quyền truy cập môn học này.',
                ], 403);
            }
        }

        $chapters = \App\Models\Chapter::where('subject_id', $subjectId)
            ->orderBy('order')
            ->get(['id', 'name']);

        return response()->json($chapters);
    }

    public function index(IndexQuestionRequest $request, QuestionBankQueryService $questionBankQueryService): View
    {
        $this->authorize('viewAny', Question::class);

        $viewData = $questionBankQueryService->getIndexData($request->validated());

        return view('question.index', $viewData);
    }

    public function create(QuestionBankQueryService $questionBankQueryService): View
    {
        $this->authorize('create', Question::class);

        return view('question.create', $questionBankQueryService->getFormData());
    }

    public function store(StoreQuestionRequest $request, QuestionBankQueryService $questionBankQueryService): RedirectResponse
    {
        $this->authorize('create', Question::class);

        $questionBankQueryService->createQuestion($request->validated(), (int) $request->user()->id);

        return redirect()->route('lecturer.questions.index')
            ->with('status', 'Tao cau hoi thanh cong.');
    }

    public function edit(Question $question, QuestionBankQueryService $questionBankQueryService): View
    {
        $this->authorize('update', $question);

        return view('question.edit', [
            ...$questionBankQueryService->getFormData(),
            'question' => $question,
        ]);
    }

    public function update(UpdateQuestionRequest $request, Question $question, QuestionBankQueryService $questionBankQueryService): RedirectResponse
    {
        $this->authorize('update', $question);

        $questionBankQueryService->updateQuestion($question, $request->validated());

        return redirect()->route('lecturer.questions.index')
            ->with('status', 'Cap nhat cau hoi thanh cong.');
    }

    public function destroy(Question $question, QuestionBankQueryService $questionBankQueryService): RedirectResponse
    {
        $this->authorize('delete', $question);

        $questionBankQueryService->deleteQuestion($question);

        return redirect()->route('lecturer.questions.index')
            ->with('status', 'Da xoa cau hoi.');
    }

    public function export(IndexQuestionRequest $request, QuestionBankQueryService $questionBankQueryService): StreamedResponse
    {
        $this->authorize('viewAny', Question::class);

        $filters = $request->validated();
        $rows = $questionBankQueryService->getFilteredQuestionsQuery($filters)
            ->with(['subject:id,name', 'chapter:id,name', 'questionType:id,name'])
            ->orderBy('id')
            ->get();

        $fileName = 'question-bank-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', 'Noi dung', 'Mon hoc', 'Chuong', 'Loai cau hoi', 'Muc do', 'Trang thai', 'Cap nhat luc']);

            foreach ($rows as $question) {
                fputcsv($output, [
                    $question->id,
                    trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $question->content))),
                    $question->subject?->name,
                    $question->chapter?->name,
                    $question->questionType?->name,
                    $question->difficulty,
                    $question->status,
                    optional($question->updated_at)?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
