<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Difficulty;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

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
            ->whereHas('courseSections', fn($q) => $q->where('lecturer_id', auth()->id()))
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
            ->with(['subject:id,name', 'chapter:id,name', 'questionType:id,name,code', 'difficultyLevel:code,name'])
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
     *   difficulties: Collection<int, Difficulty>
     * }
     */
    public function getFormData(): array
    {
        $subjects = Subject::query()
            ->whereHas('courseSections', fn($q) => $q->where('lecturer_id', auth()->id()))
            ->orderedForQuestionBank()
            ->get(['id', 'name', 'code']);

        $chapters = Chapter::query()
            ->with('subject:id,name')
            ->orderedForQuestionBank()
            ->get(['id', 'subject_id', 'name']);

        $questionTypes = QuestionType::query()
            ->active()
            ->orderedForQuestionBank()
            ->get(['id', 'name', 'code']);

        $difficulties = Difficulty::query()
            ->orderedForQuestionBank()
            ->get(['code', 'name']);

        return [
            'subjects' => $subjects,
            'chapters' => $chapters,
            'questionTypes' => $questionTypes,
            'difficulties' => $difficulties,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createQuestion(array $payload, int $userId): Question
    {
        $payload['created_by'] = $userId;

        return \Illuminate\Support\Facades\DB::transaction(function () use ($payload) {
            $question = Question::create($payload);

            if (isset($payload['options']) && is_array($payload['options'])) {
                $correctOptions = $payload['correct_options'] ?? [];
                foreach ($payload['options'] as $index => $optionData) {
                    $question->options()->create([
                        'label'      => \App\Models\QuestionOption::class ? chr(65 + $index) : '', // Assuming A,B,C,D labeling logic or adjust to your need.
                        'content'    => $optionData['content'],
                        'is_correct' => in_array((string)$index, $correctOptions, true) || in_array((int)$index, $correctOptions, true),
                        'order'      => $index,
                    ]);
                }
            }

            return $question;
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateQuestion(Question $question, array $payload): Question
    {
        $this->assertQuestionCanBeUpdated($question);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($question, $payload) {
            $question->update($payload);

            if (isset($payload['options']) && is_array($payload['options'])) {
                // Remove old options and recreate for simplicity, or sync.
                $question->options()->delete();
                $correctOptions = $payload['correct_options'] ?? [];

                // Trimming the correctOptions to flat structure just in case it's an array of strings
                foreach ($payload['options'] as $index => $optionData) {
                    $question->options()->create([
                        'label'      => chr(65 + $index),
                        'content'    => $optionData['content'],
                        'is_correct' => in_array((string)$index, $correctOptions, true) || in_array((int)$index, $correctOptions, true),
                        'order'      => $index,
                    ]);
                }
            }

            return $question;
        });
    }

    private function assertQuestionCanBeUpdated(Question $question): void
    {
        $isBeingAttempted = ExamAttempt::query()
            ->inProgress()
            ->where('current_question_id', $question->id)
            ->exists();

        if ($isBeingAttempted) {
            throw ValidationException::withMessages([
                'question' => 'Khong the cap nhat cau hoi nay vi co sinh vien dang lam den cau nay.',
            ]);
        }
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

        return Question::query()
            ->when($subjectCode, function (Builder $query) use ($subjectCode) {
                $query->whereHas('subject', function (Builder $subjectQuery) use ($subjectCode) {
                    $subjectQuery->where('code', $subjectCode);
                });
            })
            ->when($chapterId, fn(Builder $query) => $query->where('chapter_id', $chapterId))
            ->when($difficultyFilter, fn(Builder $query) => $query->where('difficulty', $difficultyFilter));
    }
}
