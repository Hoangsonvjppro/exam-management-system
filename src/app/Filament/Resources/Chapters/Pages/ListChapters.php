<?php

namespace App\Filament\Resources\Chapters\Pages;

use App\Filament\Imports\ChapterImporter;
use App\Filament\Resources\Chapters\ChapterResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
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
            ImportAction::make('import_chapters')
                ->importer(ChapterImporter::class)
                ->label('Import chương')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Import chương từ CSV'),
        ];
    }
}
