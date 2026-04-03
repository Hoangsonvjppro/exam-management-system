<?php

namespace App\Filament\Resources\Semesters\Pages;

use App\Filament\Resources\Semesters\SemesterResource;
use App\Services\SemesterLifecycleService;
use App\Services\SemesterValidationService;
use Filament\Resources\Pages\CreateRecord;

class CreateSemester extends CreateRecord
{
    protected static string $resource = SemesterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        app(SemesterValidationService::class)->validateForUpsert($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(SemesterLifecycleService::class)->syncAll();
    }
}
