<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExamScheduleStudent — Phân SV vào ca thi.
 *
 * @property int    $id
 * @property int    $exam_schedule_id
 * @property int    $student_id
 * @property int    $seat_number
 * @property string $attendance_status  pending|present|absent
 */
class ExamScheduleStudent extends Model
{
    protected $fillable = [
        'exam_schedule_id',
        'student_id',
        'attendance_status',
    ];

    // remove casts entirely since seat_number was the only one

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
