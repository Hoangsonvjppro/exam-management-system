<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class QuestionBankQueryService
{
    private const DIFFICULTY_MAP = [
        'easy' => ['easy', 'remember'],
        'medium' => ['medium', 'understand'],
        'hard' => ['hard', 'apply', 'analyze'],
    ];

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *   subjects: Collection<int, Subject>,
     *   chapters: Collection<int, Chapter>,
     *   questions: LengthAwarePaginator
     * }
     */
    public function getIndexData(array $filters): array
    {
        $subjectCode = $filters['sub-sel-ques'] ?? null;
        $chapterId = $filters['chap-sel-ques'] ?? null;
        $difficultyFilter = $filters['diff-sel-ques'] ?? null;
        $status = $filters['status-sel-ques'] ?? null;

        $subjects = Subject::query()
            ->orderedForQuestionBank()
            ->get();

        $chapters = Chapter::query()
            ->orderedForQuestionBank()
            ->forSubjectCode($subjectCode)
            ->get();

        $questions = Question::query()
            ->with(['subject:id,name', 'chapter:id,name'])
            ->when($subjectCode, function ($query) use ($subjectCode) {
                $query->whereHas('subject', function ($subjectQuery) use ($subjectCode) {
                    $subjectQuery->where('code', $subjectCode);
                });
            })
            ->when($chapterId, fn($query) => $query->where('chapter_id', $chapterId))
            ->when($status, fn($query) => $query->where('status', $status))
            ->when($difficultyFilter, function ($query) use ($difficultyFilter) {
                $mappedDifficulties = self::DIFFICULTY_MAP[$difficultyFilter] ?? [$difficultyFilter];
                $query->whereIn('difficulty', $mappedDifficulties);
            })
            ->latest('updated_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return [
            'subjects' => $subjects,
            'chapters' => $chapters,
            'questions' => $questions,
        ];
    }
}
