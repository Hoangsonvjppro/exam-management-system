<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSectionStudent extends Model
{
    protected $table    = 'course_section_students';
    protected $fillable = ['course_section_id', 'student_id', 'status'];


    // ── Relationships ──────────────────────────────────────────

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // ── Import helper ──────────────────────────────────────────

    /**
     * Find existing enrollment or build a new one (unsaved).
     */
    public static function findOrNewByEnrollment(int $sectionId, int $studentId): static
    {
        return static::firstOrNew([
            'course_section_id' => $sectionId,
            'student_id'        => $studentId,
        ]);
    }
}