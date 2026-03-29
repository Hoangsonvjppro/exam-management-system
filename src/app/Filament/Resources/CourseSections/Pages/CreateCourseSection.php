<?php

namespace App\Filament\Resources\CourseSections\Pages;

use App\Filament\Resources\CourseSections\CourseSectionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateCourseSection extends CreateRecord
{
    protected static string $resource = CourseSectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = CourseSectionResource::generateCourseSectionCode(
            $data['subject_id'] ?? null,
            $data['semester_id'] ?? null,
        ) ?? strtoupper((string) ($data['code'] ?? ''));
        $data['invite_code'] = filled($data['invite_code'] ?? null)
            ? strtoupper((string) $data['invite_code'])
            : strtoupper(Str::random(8));

        // Auto-assign current lecturer
        $data['lecturer_id'] = auth()->id();

        return $data;
    }
}
