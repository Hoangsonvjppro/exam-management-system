<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm quản trị viên mới')
                ->icon('heroicon-m-plus')
                ->successNotificationTitle('Đã thêm quản trị viên mới thành công')
                ->modalHeading('Thêm quản trị viên mới')
                ->modalWidth(Width::ThreeExtraLarge),
        ];
    }
}
