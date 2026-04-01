<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\ExamAttemptStatus;
use App\Enums\ExamStatus;
use App\Enums\ExamType;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    // Các constant status/type đã được chuyển qua Enum

    protected $fillable = [
        'subject_id',
        'created_by',
        'title',
        'description',
        'duration_minutes',
        'allow_late_entrance',
        'late_entrance_limit_minutes',
        'late_entrance_behavior',
        'min_duration_before_submit',
        'status',
        'exam_type',
        'reopen_reason',
        'multiple_choice_scoring_method',
        'total_points',
        'pass_points',
        'show_score_after_submit',
        'show_answers_after_submit',
    ];

    protected $casts = [
        'allow_late_entrance' => 'boolean',
        'late_entrance_limit_minutes' => 'integer',
        'min_duration_before_submit' => 'integer',
        'total_points' => 'decimal:2',
        'pass_points' => 'decimal:2',
        'show_score_after_submit' => 'boolean',
        'show_answers_after_submit' => 'boolean',
        'status' => ExamStatus::class,
        'exam_type' => ExamType::class,
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order_index');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot('points', 'order_index')
            ->withTimestamps();
    }

    public function attempts(): HasManyThrough
    {
        return $this->hasManyThrough(ExamAttempt::class, ExamSchedule::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function matrices(): HasMany
    {
        return $this->hasMany(ExamMatrix::class);
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ExamStatus::Published);
    }

    public function scopeForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }


    // ── Accessors ─────────────────────────────────────────────

    // Các hàm tính thời gian đã được chuyển sang ExamSchedule.

    public function isCompletedBy(int $userId): bool
    {
        return $this->attempts()
            ->where('exam_attempts.user_id', $userId)
            ->where('exam_attempts.status', ExamAttemptStatus::Completed)
            ->exists();
    }

    public function isPractice(): bool
    {
        return $this->exam_type === ExamType::Practice;
    }

    public function isOfficial(): bool
    {
        return $this->exam_type === ExamType::Official;
    }

    /**
     * Kiểm tra transition trạng thái hợp lệ.
     * draft → published → closed → published (reopen)
     */
    public function canTransitionTo(ExamStatus $newStatus): bool
    {
        $allowed = [
            ExamStatus::Draft->value     => [ExamStatus::Published],
            ExamStatus::Published->value => [ExamStatus::Closed],
            ExamStatus::Closed->value    => [ExamStatus::Published], // reopen
        ];

        return in_array($newStatus, $allowed[$this->status->value] ?? []);
    }

    /**
     * Kiểm tra đề có thể sửa cấu trúc (câu hỏi, thời gian) không.
     * Chỉ sửa được khi chưa có ai thi.
     */
    public function canEditStructure(): bool
    {
        return $this->attempts()->doesntExist();
    }
}
