<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ClassSchedule Model — Thời khóa biểu chi tiết.
 *
 * @property int    $id
 * @property int    $course_section_id
 * @property int    $day_of_week    2=Thứ Hai ... 8=Chủ Nhật
 * @property int    $start_period   Tiết bắt đầu (1-16)
 * @property int    $end_period     Tiết kết thúc (1-16)
 * @property string $room           Phòng học
 */
class ClassSchedule extends Model
{
    protected $fillable = [
        'course_section_id',
        'day_of_week',
        'start_period',
        'end_period',
        'room',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'start_period' => 'integer',
            'end_period' => 'integer',
        ];
    }

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    /**
     * Tên ngày trong tuần (tiếng Việt).
     */
    public function getDayNameAttribute(): string
    {
        $days = [
            2 => 'Thứ Hai',
            3 => 'Thứ Ba',
            4 => 'Thứ Tư',
            5 => 'Thứ Năm',
            6 => 'Thứ Sáu',
            7 => 'Thứ Bảy',
            8 => 'Chủ Nhật',
        ];

        return $days[$this->day_of_week] ?? '';
    }
}
