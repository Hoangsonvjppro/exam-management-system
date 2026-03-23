<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

class QuestionBankQueryService
{
    /**
     * @param array<string, mixed> $filters
     * @return array{subjects: Collection<int, Subject>, chapters: Collection<int, Chapter>}
     */
    public function getIndexData(array $filters): array
    {
        $subjects = Subject::query()
            ->orderedForQuestionBank()
            ->get();

        $chapters = Chapter::query()
            ->orderedForQuestionBank()
            ->forSubjectCode($filters['sub-sel-ques'] ?? null)
            ->get();

        return [
            'subjects' => $subjects,
            'chapters' => $chapters,
        ];
    }
}
