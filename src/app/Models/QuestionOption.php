<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * QuestionOption Model — Lựa chọn đáp án (A/B/C/D).
 *
 * @property int    $id
 * @property int    $question_id
 * @property string $label      A, B, C, D
 * @property string $content    Nội dung đáp án
 * @property bool   $is_correct
 * @property int    $order
 */
class QuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'label',
        'content',
        'image_file_id',
        'is_correct',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_file_id');
    }
}
