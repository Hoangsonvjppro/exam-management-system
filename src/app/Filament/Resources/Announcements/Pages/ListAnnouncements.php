<?php

namespace App\Filament\Resources\Announcements\Pages;

use App\Filament\Resources\Announcements\AnnouncementResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Thêm thông báo mới')
                ->icon('heroicon-m-plus')
                ->successNotificationTitle('Đã thêm thông báo thành công')
                ->modalHeading('Thêm thông báo mới')
                ->modalWidth(Width::TwoExtraLarge),
        ];
    }
}
