<?php

namespace App\Filament\Resources\CourseSections\Pages;

use App\Filament\Resources\CourseSections\CourseSectionResource;
use App\Models\CourseSection;
use App\Services\AttendanceGradeService;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListCourseSections extends ListRecords
{
    protected static string $resource = CourseSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createCourseSection')
                ->label('Thêm lớp học phần')
                ->icon('heroicon-m-plus')
                ->schema(CourseSectionResource::getFormComponents())
                ->action(function (array $data): void {
                    $data['code'] = CourseSectionResource::generateCourseSectionCode(
                        $data['subject_id'] ?? null,
                        $data['semester_id'] ?? null,
                    ) ?? strtoupper((string) ($data['code'] ?? ''));

                    $data['invite_code'] = filled($data['invite_code'] ?? null)
                        ? strtoupper((string) $data['invite_code'])
                        : strtoupper(Str::random(8));

                    $section = CourseSection::query()->create($data);

                    app(AttendanceGradeService::class)->ensureColumn($section);
                })
                ->successNotificationTitle('Đã thêm lớp học phần'),
        ];
    }
}
