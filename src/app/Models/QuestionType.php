<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * QuestionType Model — Loại câu hỏi.
 *
 * @property int    $id
 * @property string $code           multiple_choice, true_false...
 * @property string $name           Tên hiển thị
 * @property bool   $is_auto_grade  Chấm tự động?
 * @property bool   $is_active
 * @property int    $display_order
 * @property array  $answer_schema  JSON Schema
 */
class QuestionType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'answer_schema',
        'is_auto_grade',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'answer_schema' => 'array',
            'is_auto_grade' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderedForQuestionBank(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    public function scopeSearchByKeyword(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(function (Builder $questionTypeQuery) use ($keyword) {
            $questionTypeQuery->where('name', 'like', '%' . $keyword . '%')
                ->orWhere('code', 'like', '%' . $keyword . '%');
        });
    }
}
