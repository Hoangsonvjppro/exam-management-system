<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CourseSection Model — Lớp học phần.
 *
 * @property int    $id
 * @property string $code          Mã lớp: CS101-01-HK1-2526
 * @property int    $subject_id
 * @property int    $semester_id
 * @property int    $lecturer_id
 * @property int    $max_students
 * @property string $status        active|archived|cancelled
 */
class CourseSection extends Model
{
    protected $fillable = [
        'code',
        'invite_code',
        'subject_id',
        'semester_id',
        'lecturer_id',
        'max_students',
        'status',
    ];

    protected function casts(): array
    {
        return ['max_students' => 'integer'];
    }

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
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    /**
     * Sinh viên đăng ký lớp (N-N qua course_section_students).
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_section_students', 'course_section_id', 'student_id')
            ->withPivot('status', 'enrolled_at')
            ->withTimestamps();
    }

    /**
     * Lịch học chi tiết.
     */
    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
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
