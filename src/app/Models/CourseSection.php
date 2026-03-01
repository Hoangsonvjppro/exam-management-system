<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseSection extends Model
{
    protected $fillable = [
        'code', 'subject_id', 'semester_id',
        'lecturer_id', 'max_students', 'status',
    ];

    protected $casts = [
        'max_students' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function lecturer(): BelongsTo
    {
        // Tên FK khác tên model nên phải chỉ định rõ
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'course_section_students',
            'course_section_id',
            'student_id'
        )->withPivot('status', 'enrolled_at')->withTimestamps();
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ── Helpers ────────────────────────────────────────────────

    // Số sinh viên đang enrolled (không phải dropped)
    public function getEnrolledCountAttribute(): int
    {
        return $this->students()->wherePivot('status', 'enrolled')->count();
    }
}