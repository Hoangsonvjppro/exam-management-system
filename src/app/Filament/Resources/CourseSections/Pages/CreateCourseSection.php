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
        $data['code'] = strtoupper($data['code']);
        $data['invite_code'] = filled($data['invite_code'] ?? null)
            ? strtoupper((string) $data['invite_code'])
            : strtoupper(Str::random(8));

        return $data;
    }
}
