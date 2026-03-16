<?php

namespace App\Filament\Widgets;

use App\Models\CourseSection;
use App\Models\Subject;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUsers    = User::count();
        $lecturers     = User::role('lecturer')->count();
        $students      = User::role('student')->count();
        $subjects      = Subject::count();
        $activeSections = CourseSection::where('status', 'active')->count();

        return [
            Stat::make('Tổng người dùng', $totalUsers)
                ->description("{$lecturers} giảng viên · {$students} sinh viên")
                ->color('primary'),

            Stat::make('Môn học', $subjects)
                ->description('Số môn học trong hệ thống')
                ->color('success'),

            Stat::make('Lớp học phần (đang mở)', $activeSections)
                ->description('Lớp đang hoạt động')
                ->color('info'),
        ];
    }
}
