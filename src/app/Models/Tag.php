<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function scopeOrderedForQuestionBank(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function scopeSearchByKeyword(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(function (Builder $tagQuery) use ($keyword) {
            $tagQuery->where('name', 'like', '%' . $keyword . '%')
                ->orWhere('slug', 'like', '%' . $keyword . '%');
        });
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_tag_map', 'tag_id', 'question_id');
    }
}
