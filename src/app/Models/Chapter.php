<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Chapter Model — Chương (thuộc môn học).
 *
 * @property int    $id
 * @property int    $subject_id
 * @property string $name
 * @property int    $order  Thứ tự sắp xếp
 */
class Chapter extends Model
{
    protected $fillable = ['subject_id', 'name', 'order', 'description'];

    protected function casts(): array
    {
        return ['order' => 'integer'];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
