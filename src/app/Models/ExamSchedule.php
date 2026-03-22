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
}
