<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * ExamSchedule — Ca thi (lịch thi cụ thể cho 1 đề).
 *
 * @property int    $id
 * @property int    $exam_id
 * @property int    $exam_room_id
 * @property string $exam_date
 * @property string $start_time
 * @property string $end_time
 * @property int    $max_students
 * @property string $status     scheduled|in_progress|completed|cancelled
 */
class ExamSchedule extends Model
{
    protected $fillable = [
        'exam_id',
        'course_section_id',
        'exam_date',
        'start_time',
        'end_time',
        'max_students',
        'notes',
        'status',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'max_students' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function scheduleStudents(): HasMany
    {
        return $this->hasMany(ExamScheduleStudent::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'exam_schedule_students', 'exam_schedule_id', 'student_id')
            ->withPivot('seat_number', 'attendance_status')
            ->withTimestamps();
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('exam_date', '>=', now()->toDateString())
            ->where('status', 'scheduled');
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getAssignedCountAttribute(): int
    {
        return $this->scheduleStudents()->count();
    }

    /**
     * Tính deadline thực tế cho một attempt trong ca thi này.
     * Dựa theo cấu hình late_entrance_behavior của exam.
     */
    public function getDeadlineFor(ExamAttempt $attempt): Carbon
    {
        $durationMinutes = $this->exam->duration_minutes ?? 0;
        $durationEnd = $attempt->started_at->copy()->addMinutes($durationMinutes);

        // Nếu cấu hình là flexible_duration, được làm đủ thời gian (kể cả khi vào muộn và qua giờ end_time)
        if ($this->exam->late_entrance_behavior === 'flexible_duration') {
            return $durationEnd;
        }

        // Mặc định (fixed_end): Deadline = min(started_at + exam->duration, schedule.end_time)
        if ($this->exam_date && $this->end_time) {
            // $scheduleEnd = Carbon::parse($this->exam_date->format('Y-m-d') . ' ' . $this->end_time);
            $scheduleEnd = Carbon::parse($this->exam_date)->copy()->setTimeFromTimeString($this->end_time);
            return $durationEnd->lt($scheduleEnd) ? $durationEnd : $scheduleEnd;
        }

        return $durationEnd;
    }

    public function getStartDatetimeAttribute(): Carbon
    {
        return Carbon::parse($this->exam_date->format('Y-m-d') . ' ' . $this->start_time);
    }

    public function getEndDatetimeAttribute(): Carbon
    {
        return Carbon::parse($this->exam_date->format('Y-m-d') . ' ' . $this->end_time);
    }

    public function getIsNotStartedAttribute(): bool
    {
        return now()->lt($this->start_datetime);
    }

    public function getIsOverAttribute(): bool
    {
        return now()->gt($this->end_datetime);
    }

    public function getTimeLeftMinutesAttribute(): int
    {
        if ($this->is_not_started || $this->is_over) {
            return 0;
        }
        return (int) now()->diffInMinutes($this->end_datetime, false);
    }

    public function getTimeLeftTextAttribute(): string
    {
        $minutes = $this->time_left_minutes;
        if ($minutes <= 0) return 'Đã hết giờ';
        if ($minutes < 60) return "Còn {$minutes} phút";

        $hours = floor($minutes / 60);
        $rem = $minutes % 60;
        return "Còn {$hours}g {$rem}p";
    }

    public function isCompletedBy(int $userId): bool
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->completed() // Dùng scope completed() của ExamAttempt
            ->exists();
    }
}
