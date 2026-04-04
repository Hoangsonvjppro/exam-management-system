<?php

namespace App\Filament\Resources\Subjects\Pages;

use App\Filament\Imports\SubjectImporter;
use App\Filament\Resources\Subjects\SubjectResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm môn học mới')
                ->icon('heroicon-m-plus')
                ->successNotificationTitle('Đã thêm môn học thành công')
                ->modalHeading('Thêm môn học mới')
                ->modalWidth(Width::TwoExtraLarge),
            ImportAction::make('import_subjects')
                ->importer(SubjectImporter::class)
                ->label('Import môn học')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->modalHeading('Import môn học từ CSV'),
        ];
    }
}
