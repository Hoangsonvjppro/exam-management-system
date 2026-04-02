<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Semester Model — Học kỳ.
 *
 * @property int    $id
 * @property string $name       VD: HK1 2025-2026
 * @property int    $year       Năm học bắt đầu
 * @property int    $term       1=HK1, 2=HK2, 3=HK Hè
 * @property string $start_date
 * @property string $end_date
 * @property bool   $is_current
 */
class Semester extends Model
{
    protected $fillable = [
        'name',
        'year',
        'term',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'term' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }
}
