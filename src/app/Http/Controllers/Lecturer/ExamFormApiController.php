<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API Controller phục vụ AJAX cho form tạo đề thi.
 *
 * Cung cấp:
 * - Danh sách câu hỏi (paginated, filterable)
 * - Availability map (số câu hỏi khả dụng theo chapter × difficulty)
 * - Tạo câu hỏi nhanh (trả JSON)
 */
class ExamFormApiController extends Controller
{
    /**
     * GET /lecturer/api/exam-form/questions
     *
     * Trả về danh sách câu hỏi approved, lọc theo subject, chapter, difficulty, keyword.
     * Phân trang kiểu "Load More" (page + per_page).
     */
    public function questions(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'chapter_id' => 'nullable|integer|exists:chapters,id',
            'difficulty' => 'nullable|string|in:remember,understand,apply,analyze',
            'keyword'    => 'nullable|string|max:255',
            'page'       => 'nullable|integer|min:1',
            'per_page'   => 'nullable|integer|min:5|max:50',
        ]);

        // Verify subject is assigned to this lecturer
        $lecturerSubjectIds = Auth::user()->courseSections()->pluck('subject_id')->unique()->toArray();
        $subjectId = (int) $request->input('subject_id');

        if (!in_array($subjectId, $lecturerSubjectIds)) {
            return response()->json(['error' => 'Bạn không có quyền truy cập môn học này.'], 403);
        }

        $perPage = (int) ($request->input('per_page', 20));

        $query = Question::with(['chapter:id,name', 'options' => fn($q) => $q->orderBy('order')])
            ->where('subject_id', $subjectId)
            ->where('status', 'approved');

        if ($request->filled('chapter_id')) {
            $query->where('chapter_id', $request->input('chapter_id'));
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->input('difficulty'));
        }

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('content', 'like', '%' . $keyword . '%');
        }

        $paginated = $query->orderBy('id', 'desc')->paginate($perPage);

        $items = $paginated->getCollection()->map(fn(Question $q) => [
            'id'         => $q->id,
            'content'    => $q->content,
            'difficulty' => $q->difficulty,
            'chapter'    => $q->chapter ? ['id' => $q->chapter->id, 'name' => $q->chapter->name] : null,
            'options'    => $q->options->map(fn($opt) => [
                'label'      => $opt->label,
                'content'    => $opt->content,
                'is_correct' => $opt->is_correct,
            ])->toArray(),
        ]);

        return response()->json([
            'data'         => $items,
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'per_page'     => $paginated->perPage(),
            'total'        => $paginated->total(),
        ]);
    }

    /**
     * GET /lecturer/api/exam-form/availability
     *
     * Trả về availability map: số câu hỏi approved theo từng (chapter_id, difficulty).
     * Key format: "chapter_id|difficulty" (chapter_id = "null" nếu không gán chương).
     */
    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        $lecturerSubjectIds = Auth::user()->courseSections()->pluck('subject_id')->unique()->toArray();
        $subjectId = (int) $request->input('subject_id');

        if (!in_array($subjectId, $lecturerSubjectIds)) {
            return response()->json(['error' => 'Bạn không có quyền truy cập môn học này.'], 403);
        }

        $counts = Question::where('subject_id', $subjectId)
            ->where('status', 'approved')
            ->selectRaw('COALESCE(chapter_id, 0) as ch_id, difficulty, COUNT(*) as cnt')
            ->groupBy('ch_id', 'difficulty')
            ->get();

        $map = [];
        foreach ($counts as $row) {
            $chapterKey = $row->ch_id == 0 ? 'null' : (string) $row->ch_id;
            $map[$chapterKey . '|' . $row->difficulty] = (int) $row->cnt;
        }

        // Also provide totals per difficulty (all chapters)
        $totals = Question::where('subject_id', $subjectId)
            ->where('status', 'approved')
            ->selectRaw('difficulty, COUNT(*) as cnt')
            ->groupBy('difficulty')
            ->pluck('cnt', 'difficulty')
            ->toArray();

        return response()->json([
            'map'    => $map,
            'totals' => $totals,
        ]);
    }

    /**
     * POST /lecturer/api/exam-form/quick-question
     *
     * Tạo câu hỏi nhanh, trả JSON thay vì redirect HTML.
     */
    public function quickQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id'       => 'required|integer|exists:subjects,id',
            'chapter_id'       => 'nullable|integer|exists:chapters,id',
            'question_type_id' => 'required|integer|exists:question_types,id',
            'content'          => 'required|string|min:5',
            'difficulty'       => 'required|string|in:remember,understand,apply,analyze',
            'status'           => 'required|string|in:draft,approved,hidden',
            'explanation'      => 'nullable|string',
        ]);

        // Verify subject is assigned to this lecturer
        $lecturerSubjectIds = Auth::user()->courseSections()->pluck('subject_id')->unique()->toArray();
        if (!in_array((int) $validated['subject_id'], $lecturerSubjectIds)) {
            return response()->json(['error' => 'Bạn không có quyền tạo câu hỏi cho môn học này.'], 403);
        }

        $validated['created_by'] = Auth::id();

        $question = Question::create($validated);

        return response()->json([
            'id'         => $question->id,
            'content'    => $question->content,
            'difficulty' => $question->difficulty,
            'subject_id' => $question->subject_id,
            'chapter_id' => $question->chapter_id,
        ], 201);
    }
}
