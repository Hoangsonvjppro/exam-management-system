<?php

namespace App\Filament\Resources\Semesters\Pages;

use App\Filament\Resources\Semesters\SemesterResource;
use App\Models\Semester;
use App\Services\SemesterGovernanceService;
use App\Services\SemesterLifecycleService;
use App\Services\SemesterValidationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditSemester extends EditRecord
{
    protected static string $resource = SemesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('archive')
                ->label('Lưu trữ')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Lưu trữ học kỳ')
                ->modalDescription('Chỉ lưu trữ khi học kỳ đã kết thúc và không còn ca thi đang mở.')
                ->visible(fn(): bool => $this->record->status !== Semester::STATUS_ARCHIVED)
                ->action(function (): void {
                    try {
                        app(SemesterGovernanceService::class)->archiveSemester($this->record);

                        Notification::make()
                            ->title('Đã lưu trữ học kỳ')
                            ->success()
                            ->send();

                        $this->record->refresh();
                    } catch (ValidationException $exception) {
                        $message = (string) (collect($exception->errors())->flatten()->first()
                            ?? 'Không thể lưu trữ học kỳ ở thời điểm hiện tại.');

                        Notification::make()
                            ->title('Không thể lưu trữ học kỳ')
                            ->body($message)
                            ->danger()
                            ->send();
                    }
                }),
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
