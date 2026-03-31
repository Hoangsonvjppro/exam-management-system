<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageDepartments extends ManageRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm khoa')
                ->icon('heroicon-m-plus')
                ->successNotificationTitle('Đã thêm khoa thành công')
                ->modalHeading('Thêm khoa mới')
                ->modalWidth(Width::Medium),
        ];
    }
}
