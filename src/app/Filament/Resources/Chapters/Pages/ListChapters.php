<?php

namespace App\Filament\Resources\Chapters\Pages;

use App\Filament\Resources\Chapters\ChapterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListChapters extends ListRecords
{
    protected static string $resource = ChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Thêm chương mới')
                ->icon('heroicon-m-plus')
                ->successNotificationTitle('Đã thêm chương mới thành công')
                ->modalHeading('Thêm chương mới')
                ->modalWidth(Width::Medium),
        ];
    }
}
