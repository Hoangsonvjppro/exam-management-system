<?php

namespace App\Filament\Resources\Semesters\Pages;

use App\Filament\Resources\Semesters\SemesterResource;
use App\Services\SemesterLifecycleService;
use App\Services\SemesterValidationService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSemester extends EditRecord
{
    protected static string $resource = SemesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->disabled(fn(): bool => $this->record->courseSections()->exists())
                ->tooltip(fn(): ?string => $this->record->courseSections()->exists()
                    ? 'Không thể xóa học kỳ đã phát sinh lớp học phần.'
                    : null),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        app(SemesterValidationService::class)->validateForUpsert($data, (int) $this->record->getKey());

        return $data;
    }

    protected function afterSave(): void
    {
        app(SemesterLifecycleService::class)->syncAll();
    }
}
