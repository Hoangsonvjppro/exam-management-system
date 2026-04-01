<?php

namespace App\Filament\Resources\CourseSections\Pages;

use App\Filament\Resources\CourseSections\CourseSectionResource;
use App\Filament\Resources\CourseSections\Students\StudentTable;
use App\Models\User;
use App\Services\AttendanceGradeService;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;

class ManageCourseSectionStudents extends ManageRelatedRecords
{
    protected static string $resource = CourseSectionResource::class;

    protected static string $relationship = 'students';

    protected static ?string $title = 'Sinh viên trong lớp';

    public function getTitle(): string
    {
        $courseSection = $this->getRecord();
        return "Sinh viên trong lớp " . ($courseSection->code ?? '');
    }

    protected static ?string $navigationLabel = 'Sinh viên';

    public function table(Table $table): Table
    {
        return StudentTable::configure($table, $this);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm sinh viên')
                ->icon('heroicon-m-plus')
                ->modalHeading('Chọn sinh viên')
                ->modalSubmitActionLabel('Thêm')
                ->createAnotherAction(
                    fn($action) => $action->label('Thêm và tiếp tục')
                )
                ->modalCancelActionLabel('Đóng')
                ->schema([
                    Select::make('student_id')
                        ->label('Sinh viên')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(function () {
                            return User::query()
                                ->role('student')
                                ->get()
                                ->mapWithKeys(function ($user) {
                                    return [
                                        $user->id => "{$user->name} ({$user->student_code})",
                                    ];
                                });
                        }),
                ])
                ->using(function (array $data): User {
                    $student = User::findOrFail($data['student_id']);
                    $section = $this->getRecord();

                    $section->students()->syncWithoutDetaching([
                        $student->id => [
                            'status' => 'enrolled',
                            'enrolled_at' => now(),
                        ],
                    ]);

                    app(AttendanceGradeService::class)
                        ->ensureScoreForStudent($section, $student->id, auth()->id());

                    return $student;
                }),
        ];
    }
}
