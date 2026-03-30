<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_section_id',
        'name',
        'weight',
        'is_exam_linked',
        'exam_schedule_id',
        'order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_exam_linked' => 'boolean',
        'order' => 'integer',
        'exam_schedule_id' => 'integer',
    ];

    /**
     * Get the course section this grade column belongs to.
     */
    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    /**
     * Get the linked exam schedule, if any.
     */
    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    /**
     * Get all student grades for this column.
     */
    public function studentGrades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }
}
