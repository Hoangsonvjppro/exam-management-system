<?php

namespace App\Filament\Resources\Lecturers\Pages;

use App\Filament\Resources\Lecturers\Subjects\SubjectForm;
use App\Filament\Resources\Lecturers\Subjects\SubjectTable;
use App\Filament\Resources\Lecturers\LecturersResource;
use App\Models\Subject;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;

class ManageLecturerSubjects extends ManageRelatedRecords
{
    protected static string $resource = LecturersResource::class;

    protected static string $relationship = 'subjects';

    protected static ?string $title = 'Môn học của giảng viên';

    public function getTitle(): string
    {
        $lecturer = $this->getRecord();
        return "Môn học của giảng viên " . ($lecturer->name ?? '');
    }

    protected static ?string $navigationLabel = 'Môn học';

    public static function canAccess(array $parameters = []): bool
    {
        return parent::canAccess($parameters) && LecturersResource::canAssignLecturerSubject();
    }

    public function table(Table $table): Table
    {
        return SubjectTable::configure($table, $this);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Phân công môn học')
                ->icon('heroicon-m-plus')
                ->authorize(fn(): bool => LecturersResource::canAssignLecturerSubject())
                ->modalHeading('Chọn môn học')
                ->modalSubmitActionLabel('Phân công')
                ->createAnotherAction(
                    fn($action) => $action->label('Phân công và tiếp tục')
                )
                ->modalCancelActionLabel('Đóng')
                ->schema(SubjectForm::make($this->getRecord()->id))
                ->using(function (array $data): Subject {
                    $subject = Subject::findOrFail($data['subject_id']);
                    $lecturer = $this->getRecord();

                    // Gán môn học cho giảng viên
                    $lecturer->subjects()->syncWithoutDetaching([
                        $subject->id
                    ]);
                    return $subject;
                }),
        ];
    }
}
