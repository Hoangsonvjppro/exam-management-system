<?php

namespace App\Filament\Resources\StudentClasses\Pages;

use App\Filament\Resources\StudentClasses\StudentClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageStudentClasses extends ManageRecords
{
    protected static string $resource = StudentClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm lớp học mới')
                ->icon('heroicon-m-plus')
                ->successNotificationTitle('Đã thêm lớp học thành công')
                ->modalHeading('Thêm lớp học mới')
                ->modalWidth(Width::ThreeExtraLarge),
        ];
    }
}
