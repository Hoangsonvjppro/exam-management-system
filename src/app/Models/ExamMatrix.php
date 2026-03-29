<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExamMatrix — Cấu trúc ma trận đề thi.
 *
 * Mỗi row định nghĩa: chương + độ khó + loại câu → số lượng + điểm/câu.
 *
 * @property int    $id
 * @property int    $exam_id
 * @property int    $chapter_id
 * @property string $difficulty
 * @property int    $question_type_id
 * @property int    $question_count
 * @property float  $points_each
 */
class ExamMatrix extends Model
{
    protected $fillable = [
        'exam_id',
        'chapter_id',
        'difficulty',
        'question_type_id',
        'question_count',
        'points_each',
    ];

    protected $casts = [
        'question_count' => 'integer',
        'points_each' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }
}
