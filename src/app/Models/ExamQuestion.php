<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{

    protected $fillable = [
        'exam_id',
        'question_id',
        'points',
        'order_index',
        'question_snapshot',
    ];

    protected $casts = [
        'question_snapshot' => 'array',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Trả về snapshot nếu có, nếu không thì trả về question gốc.
     * Dùng để đọc nội dung câu hỏi tại thời điểm tạo đề.
     */
    public function getSnapshotAttribute(): ?array
    {
        return $this->question_snapshot;
    }
}
