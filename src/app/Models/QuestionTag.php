<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * QuestionTag Model — Nhãn/Tag cho câu hỏi.
 *
 * @property int    $id
 * @property int    $question_id
 * @property string $tag
 */
class QuestionTag extends Model
{
    public $timestamps = false;

    protected $fillable = ['question_id', 'tag'];

    /**
     * Chỉ có cột created_at, không có updated_at.
     */
    const UPDATED_AT = null;

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
