<?php

namespace App\Filament\Resources\Semesters\Pages;

use App\Filament\Resources\Semesters\SemesterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListSemesters extends ListRecords
{
    protected static string $resource = SemesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm học kỳ mới')
                ->icon('heroicon-m-plus')
                ->successNotificationTitle('Đã thêm học kỳ thành công')
                ->modalHeading('Thêm học kỳ mới')
                ->modalWidth(Width::TwoExtraLarge),
        ];
    }
}
