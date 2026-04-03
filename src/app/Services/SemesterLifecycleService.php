<?php

namespace App\Services;

use App\Models\Semester;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SemesterLifecycleService
{
    public function determineStatus(Semester $semester, ?CarbonInterface $today = null): string
    {
        if ($semester->status === Semester::STATUS_ARCHIVED) {
            return Semester::STATUS_ARCHIVED;
        }

        $today ??= now()->startOfDay();
        $startDate = Carbon::parse((string) $semester->start_date)->startOfDay();
        $endDate = Carbon::parse((string) $semester->end_date)->endOfDay();

        if ($startDate->gt($today)) {
            return Semester::STATUS_UPCOMING;
        }

        if ($endDate->lt($today)) {
            return Semester::STATUS_ENDED;
        }

        return Semester::STATUS_CURRENT;
    }

    public function syncSemester(Semester $semester): Semester
    {
        $status = $this->determineStatus($semester);
        $semester->forceFill([
            'status' => $status,
            'is_current' => $status === Semester::STATUS_CURRENT,
        ])->save();

        return $semester->refresh();
    }

    public function syncAll(): Collection
    {
        return Semester::query()
            ->orderBy('start_date')
            ->get()
            ->map(function (Semester $semester): Semester {
                return $this->syncSemester($semester);
            });
    }

    public function canCreateCourseSection(Semester $semester): bool
    {
        $status = $this->determineStatus($semester);

        return in_array($status, [Semester::STATUS_CURRENT, Semester::STATUS_UPCOMING], true);
    }
}
