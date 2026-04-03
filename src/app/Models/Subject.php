<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'credits',
        'department',
        'description',
    ];

    /**
     * Các lecturer dạy môn này
     * qua pivot table assignments
     */
    public function lecturers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'assignments',
            'subject_id',
            'lecturer_id'
        )->withTimestamps();
    }
    
    public function scopeOrderedForQuestionBank(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('subject_code', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%");
        });
    }
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
