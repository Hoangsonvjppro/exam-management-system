<?php

namespace App\Filament\Resources\Lecturers\Pages;

use App\Filament\Imports\LecturerImporter;
use App\Filament\Resources\Lecturers\LecturersResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Carbon;

class ManageLecturers extends ManageRecords
{
    protected static string $resource = LecturersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('createLecturer')
                ->label('Thêm giảng viên')
                ->icon('heroicon-m-plus')
                ->mutateDataUsing(function (array $data): array {
                    if (!empty($data['date_of_birth'])) {
                        $password = Carbon::parse($data['date_of_birth'])->format('dmY');

                        $data['password'] = bcrypt($password);
                        $data['must_change_password'] = true;
                    }

                    return $data;
                })
                ->after(function ($record) {
                    $record->assignRole('lecturer');
                }),
            ImportAction::make('import_lecturers')
                ->importer(LecturerImporter::class)
                ->label('Import giảng viên')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Import giảng viên từ CSV'),
        ];
    }
}
