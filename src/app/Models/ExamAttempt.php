<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ExamAttemptStatus;

class ExamAttempt extends Model
{
    // Đã chuyển STATUS_IN_PROGRESS, STATUS_COMPLETED sang Enum

    protected $fillable = [
        'exam_id',
        'user_id',
        'attempt_number',
        'started_at',
        'completed_at',
        'status',
        'total_score',
        'ip_address',
        'user_agent',
        'submitted_answers_count',
        'tab_switch_count',
        'focus_lost_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_score' => 'decimal:2',
        'focus_lost_at' => 'array',
        'status' => ExamAttemptStatus::class,
    ];

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', ExamAttemptStatus::InProgress);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', ExamAttemptStatus::Completed);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForExam(Builder $query, int $examId): Builder
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeLatestAttempt(Builder $query): Builder
    {
        return $query->orderByDesc('attempt_number');
    }

    // ── Relationships ─────────────────────────────────────────

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class);
    }
}
