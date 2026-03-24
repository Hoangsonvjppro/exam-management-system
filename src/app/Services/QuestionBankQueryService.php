<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Difficulty;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class QuestionBankQueryService
{
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

        $subjects = Subject::query()
            ->orderedForQuestionBank()
            ->get();

        $chapters = Chapter::query()
            ->orderedForQuestionBank()
            ->forSubjectCode($subjectCode)
            ->get();

        $difficulties = Difficulty::query()
            ->orderedForQuestionBank()
            ->get(['code', 'name']);

        $questions = $this->getFilteredQuestionsQuery($filters)
            ->with(['subject:id,name', 'chapter:id,name', 'questionType:id,name', 'difficultyLevel:code,name'])
            ->latest('updated_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return [
            'subjects' => $subjects,
            'chapters' => $chapters,
            'difficulties' => $difficulties,
            'questions' => $questions,
        ];
    }

    /**
     * @return array{
     *   subjects: Collection<int, Subject>,
     *   chapters: Collection<int, Chapter>,
     *   questionTypes: Collection<int, QuestionType>,
     *   difficulties: Collection<int, Difficulty>,
     *   statuses: array<int, string>
     * }
     */
    public function getFormData(): array
    {
        $subjects = Subject::query()
            ->orderedForQuestionBank()
            ->get(['id', 'name', 'code']);

        $chapters = Chapter::query()
            ->with('subject:id,name')
            ->orderedForQuestionBank()
            ->get(['id', 'subject_id', 'name']);

        $questionTypes = QuestionType::query()
            ->active()
            ->orderedForQuestionBank()
            ->get(['id', 'name']);

        $difficulties = Difficulty::query()
            ->orderedForQuestionBank()
            ->get(['code', 'name']);

        return [
            'subjects' => $subjects,
            'chapters' => $chapters,
            'questionTypes' => $questionTypes,
            'difficulties' => $difficulties,
            'statuses' => ['draft', 'approved', 'hidden'],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createQuestion(array $payload, int $userId): Question
    {
        $payload['created_by'] = $userId;

        return Question::query()->create($payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateQuestion(Question $question, array $payload): Question
    {
        $question->fill($payload);
        $question->save();

        return $question;
    }

    public function deleteQuestion(Question $question): void
    {
        $question->delete();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getFilteredQuestionsQuery(array $filters): Builder
    {
        $subjectCode = $filters['sub-sel-ques'] ?? null;
        $chapterId = $filters['chap-sel-ques'] ?? null;
        $difficultyFilter = $filters['diff-sel-ques'] ?? null;
        $status = $filters['status-sel-ques'] ?? null;

        return Question::query()
            ->when($subjectCode, function (Builder $query) use ($subjectCode) {
                $query->whereHas('subject', function (Builder $subjectQuery) use ($subjectCode) {
                    $subjectQuery->where('code', $subjectCode);
                });
            })
            ->when($chapterId, fn(Builder $query) => $query->where('chapter_id', $chapterId))
            ->when($status, fn(Builder $query) => $query->where('status', $status))
            ->when($difficultyFilter, fn(Builder $query) => $query->where('difficulty', $difficultyFilter));
    }
}
