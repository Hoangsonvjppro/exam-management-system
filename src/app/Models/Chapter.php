<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Chapter Model — Chương (thuộc môn học).
 *
 * @property int    $id
 * @property int    $subject_id
 * @property string $name
 * @property int    $order  Thứ tự sắp xếp
 */
class Chapter extends Model
{
    protected $fillable = ['subject_id', 'name', 'order', 'description'];

    protected function casts(): array
    {
        return ['order' => 'integer'];
    }

    protected static function booted()
    {
        static::addGlobalScope('order_by_subject', function ($query) {
            $query
                ->orderBy('subject_id')
                ->orderBy('order');
        });
    }
    
    public function scopeOrderedForQuestionBank(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }

    public function scopeForSubjectCode(Builder $query, ?string $subjectCode): Builder
    {
        if (! $subjectCode) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('subject', function (Builder $subjectQuery) use ($subjectCode) {
            $subjectQuery->where('code', $subjectCode);
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
