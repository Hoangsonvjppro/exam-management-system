<?php

namespace App\Services;

use App\Models\Difficulty;
use App\Models\Tag;
use App\Models\QuestionType;
use Illuminate\Support\Collection;

class QuestionMetadataQueryService
{
    public function getDifficulties(?string $keyword = null): Collection
    {
        return Difficulty::query()
            ->searchByKeyword($keyword)
            ->orderedForQuestionBank()
            ->get(['id', 'code', 'name']);
    }

    public function getQuestionTypes(?string $keyword = null): Collection
    {
        return QuestionType::query()
            ->active()
            ->searchByKeyword($keyword)
            ->orderedForQuestionBank()
            ->get(['id', 'code', 'name']);
    }

    public function getTags(?string $keyword = null): Collection
    {
        return Tag::query()
            ->searchByKeyword($keyword)
            ->orderedForQuestionBank()
            ->get(['id', 'name', 'slug']);
    }
}
