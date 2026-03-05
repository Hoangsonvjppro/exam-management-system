<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Subject Model — Môn học.
 *
 * @property int    $id
 * @property string $code       Mã môn: CS101
 * @property string $name       Tên môn học
 * @property int    $credits    Số tín chỉ
 * @property string $department Khoa quản lý
 */
class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'credits',
        'department',
        'description',
    ];

    protected function casts(): array
    {
        return ['credits' => 'integer'];
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }
}
