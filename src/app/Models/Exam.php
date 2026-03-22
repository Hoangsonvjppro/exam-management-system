<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\ExamStatus;
use App\Enums\ExamType;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    // Các constant status/type đã được chuyển qua Enum

    protected $fillable = [
        'course_section_id',
        'title',
        'description',
        'duration_minutes',
        'start_time',
        'end_time',
        'status',
        'exam_type',
        'reopen_reason',
        'total_points',
        'pass_points',
        'show_score_after_submit',
        'show_answers_after_submit',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_points' => 'decimal:2',
        'pass_points' => 'decimal:2',
        'show_score_after_submit' => 'boolean',
        'show_answers_after_submit' => 'boolean',
        'status' => ExamStatus::class,
        'exam_type' => ExamType::class,
    ];

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
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

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
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

    public function scopeForCourseSection(Builder $query, int $courseSectionId): Builder
    {
        return $query->where('course_section_id', $courseSectionId);
    }


    // ── Accessors ─────────────────────────────────────────────

    protected function timeLeftMinutes(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->end_time) return (int) $this->duration_minutes;
            $now = now();
            if ($now->gt($this->end_time)) return 0;
            return (int) $now->diffInMinutes($this->end_time, false);
        });
    }

    protected function timeLeftText(): Attribute
    {
        return Attribute::get(function () {
            $minutes = $this->time_left_minutes;
            if ($minutes <= 0) return 'Đã hết giờ';

            if ($minutes >= 60) {
                $hours = floor($minutes / 60);
                $rem = $minutes % 60;
                return $rem > 0 ? "Còn {$hours} giờ {$rem} phút" : "Còn {$hours} giờ";
            }

            return "Còn {$minutes} phút";
        });
    }

    protected function isNotStarted(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->start_time) return false;
            return now()->lt($this->start_time);
        });
    }

    /**
     * Tính deadline thực tế cho một attempt.
     * Deadline = min(started_at + duration, exam.end_time)  (High #6)
     */
    public function getDeadlineFor(ExamAttempt $attempt): Carbon
    {
        $durationEnd = $attempt->started_at->copy()->addMinutes($this->duration_minutes);

        if ($this->end_time) {
            return $durationEnd->lt($this->end_time) ? $durationEnd : $this->end_time;
        }

        return $durationEnd;
    }

    public function isCompletedBy(int $userId): bool
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->where('status', ExamAttemptStatus::Completed)
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
