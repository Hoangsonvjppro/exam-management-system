<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StudentAnswerOption — Junction cho câu hỏi chọn nhiều đáp án.
 *
 * @property int $id
 * @property int $student_answer_id
 * @property int $question_option_id
 */
class StudentAnswerOption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_answer_id',
        'question_option_id',
    ];

    public function studentAnswer(): BelongsTo
    {
        return $this->belongsTo(StudentAnswer::class);
    }

    public function questionOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
