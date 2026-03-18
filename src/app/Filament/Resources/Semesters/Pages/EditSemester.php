<?php

namespace App\Filament\Resources\Semesters\Pages;

use App\Filament\Resources\Semesters\SemesterResource;
use App\Models\Semester;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSemester extends EditRecord
{
    protected static string $resource = SemesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['is_current'])) {
            Semester::query()
                ->whereKeyNot($this->record->getKey())
                ->update(['is_current' => false]);
        }

        return $data;
    }
}
