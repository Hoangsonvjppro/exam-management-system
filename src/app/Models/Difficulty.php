<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Difficulty extends Model
{
    protected $fillable = [
        'code',
        'name',
        'score_weight',
    ];

    protected function casts(): array
    {
        return [
            'score_weight' => 'decimal:2',
        ];
    }

    public function scopeOrderedForQuestionBank(Builder $query): Builder
    {
        return $query->orderBy('score_weight')->orderBy('name');
    }

    public function scopeSearchByKeyword(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(function (Builder $difficultyQuery) use ($keyword) {
            $difficultyQuery->where('name', 'like', '%' . $keyword . '%')
                ->orWhere('code', 'like', '%' . $keyword . '%');
        });
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'difficulty', 'code');
    }
}
