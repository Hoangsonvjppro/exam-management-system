<?php

namespace App\Filament\Resources\Majors\Pages;

use App\Filament\Resources\Majors\MajorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageMajors extends ManageRecords
{
    protected static string $resource = MajorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm ngành học')
                ->icon('heroicon-m-plus')
                ->successNotificationTitle('Đã thêm ngành thành công')
                ->modalHeading('Thêm ngành học mới')
                ->modalWidth(Width::ExtraLarge),
        ];
    }
}
