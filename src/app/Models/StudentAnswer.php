<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'question_option_id',
        'answer_text',
        'is_correct',
        'points_awarded',
    ];

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }

    /**
     * Các đáp án đã chọn (cho câu hỏi chọn nhiều).
     */
    public function selectedOptions()
    {
        return $this->hasMany(StudentAnswerOption::class);
    }
}
