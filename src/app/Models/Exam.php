<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_section_id',
        'title',
        'description',
        'duration_minutes',
        'start_time',
        'end_time',
        'status',
        'total_points',
        'pass_points',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function courseSection()
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function examQuestions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order_index');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
                    ->withPivot('points', 'order_index')
                    ->withTimestamps();
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function getTimeLeftMinutesAttribute()
    {
        if (!$this->end_time) return $this->duration_minutes;
        $now = now();
        if ($now->gt($this->end_time)) return 0;
        return $now->diffInMinutes($this->end_time, false);
    }

    public function getTimeLeftTextAttribute()
    {
        $minutes = $this->time_left_minutes;
        if ($minutes <= 0) return 'Đã hết giờ';
        
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $rem = $minutes % 60;
            return $rem > 0 ? "Còn {$hours} giờ {$rem} phút" : "Còn {$hours} giờ";
        }
        
        return "Còn {$minutes} phút";
    }

    public function getIsNotStartedAttribute()
    {
        if (!$this->start_time) return false;
        return now()->lt($this->start_time);
    }

    public function isCompletedBy($userId)
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->exists();
    }
}
