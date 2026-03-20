<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'user_id',
        'attempt_number',
        'started_at',
        'completed_at',
        'status',
        'total_score',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForExam($query, int $examId)
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeLatestAttempt($query)
    {
        return $query->orderByDesc('attempt_number');
    }

    // ── Relationships ─────────────────────────────────────────

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }
}
