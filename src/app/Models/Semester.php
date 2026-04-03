<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

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
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_CURRENT = 'current';
    public const STATUS_ENDED = 'ended';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'year',
        'term',
        'start_date',
        'end_date',
        'is_current',
        'status',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $semester): void {
            if ($semester->courseSections()->exists()) {
                throw ValidationException::withMessages([
                    'semester' => 'Không thể xóa học kỳ đã phát sinh lớp học phần.',
                ]);
            }
        });
    }

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
        $today = now()->startOfDay();

        return $query
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', '!=', self::STATUS_ARCHIVED);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        $today = now()->startOfDay();

        return $query
            ->whereDate('start_date', '>', $today)
            ->where('status', '!=', self::STATUS_ARCHIVED);
    }

    public function scopeEnded(Builder $query): Builder
    {
        $today = now()->startOfDay();

        return $query
            ->whereDate('end_date', '<', $today)
            ->where('status', '!=', self::STATUS_ARCHIVED);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeOpenForCourseSectionCreation(Builder $query): Builder
    {
        $today = now()->startOfDay();

        return $query
            ->where('status', '!=', self::STATUS_ARCHIVED)
            ->whereDate('end_date', '>=', $today);
    }

    public function isCurrentPeriod(): bool
    {
        $today = now()->startOfDay();
        $startDate = Carbon::parse((string) $this->start_date)->startOfDay();
        $endDate = Carbon::parse((string) $this->end_date)->endOfDay();

        return $this->status !== self::STATUS_ARCHIVED
            && $startDate->lte($today)
            && $endDate->gte($today);
    }

    public function isUpcomingPeriod(): bool
    {
        $today = now()->startOfDay();
        $startDate = Carbon::parse((string) $this->start_date)->startOfDay();

        return $this->status !== self::STATUS_ARCHIVED
            && $startDate->gt($today);
    }

    public function isEndedPeriod(): bool
    {
        $today = now()->startOfDay();
        $endDate = Carbon::parse((string) $this->end_date)->endOfDay();

        return $this->status !== self::STATUS_ARCHIVED
            && $endDate->lt($today);
    }

    public function allowsCourseSectionCreation(): bool
    {
        return $this->isCurrentPeriod() || $this->isUpcomingPeriod();
    }

    public function allowsExamScheduling(): bool
    {
        return $this->allowsCourseSectionCreation();
    }

    public function allowsGradeEditing(): bool
    {
        return $this->isCurrentPeriod();
    }

    protected function lifecycleStatus(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->status === self::STATUS_ARCHIVED) {
                return self::STATUS_ARCHIVED;
            }

            if ($this->isCurrentPeriod()) {
                return self::STATUS_CURRENT;
            }

            if ($this->isUpcomingPeriod()) {
                return self::STATUS_UPCOMING;
            }

            return self::STATUS_ENDED;
        });
    }

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }
}
