<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Deadline = min(started_at + exam->duration, schedule.end_time)
     */
    public function getDeadlineFor(ExamAttempt $attempt): \Carbon\Carbon
    {
        $durationMinutes = $this->exam->duration_minutes ?? 0;
        $durationEnd = $attempt->started_at->copy()->addMinutes($durationMinutes);

        if ($this->exam_date && $this->end_time) {
            $scheduleEnd = \Carbon\Carbon::parse($this->exam_date->format('Y-m-d') . ' ' . $this->end_time);
            return $durationEnd->lt($scheduleEnd) ? $durationEnd : $scheduleEnd;
        }

        return $durationEnd;
    }
}
