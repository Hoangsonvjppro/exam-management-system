<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Question Model — Ngân hàng câu hỏi.
 *
 * @property int    $id
 * @property int    $subject_id
 * @property int    $chapter_id
 * @property int    $question_type_id
 * @property int    $created_by
 * @property string $content
 * @property string $difficulty     remember|understand|apply|analyze
 * @property string $status         draft|approved|hidden
 * @property int    $version
 * @property array  $answer_data    JSON cho fill_blank, matching...
 */
class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject_id',
        'chapter_id',
        'question_type_id',
        'created_by',
        'content',
        'difficulty',
        'image_file_id',
        'explanation',
        'answer_data',
        'status',
        'version',
        'usage_count',
        'correct_rate',
    ];

    protected function casts(): array
    {
        return [
            'answer_data' => 'array',
            'version' => 'integer',
            'usage_count' => 'integer',
            'correct_rate' => 'decimal:2',
        ];
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeApprovedForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('status', 'approved')->where('subject_id', $subjectId);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    // ── Relationships ─────────────────────────────────────────

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_file_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'question_tag_map', 'question_id', 'tag_id');
    }
}
