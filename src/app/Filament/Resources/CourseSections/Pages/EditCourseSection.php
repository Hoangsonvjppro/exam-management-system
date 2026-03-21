<?php

namespace App\Filament\Resources\CourseSections\Pages;

use App\Filament\Resources\CourseSections\CourseSectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseSection extends EditRecord
{
    protected static string $resource = CourseSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = CourseSectionResource::generateCourseSectionCode(
            $data['subject_id'] ?? null,
            $data['semester_id'] ?? null,
        ) ?? strtoupper((string) ($data['code'] ?? ''));

        if (! empty($data['invite_code'])) {
            $data['invite_code'] = strtoupper((string) $data['invite_code']);
        }

        return $data;
    }
}
