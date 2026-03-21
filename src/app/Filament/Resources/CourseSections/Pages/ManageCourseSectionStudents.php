<?php

namespace App\Filament\Resources\CourseSections\Pages;

use App\Filament\Resources\CourseSections\CourseSectionResource;
use App\Filament\Resources\CourseSections\Students\StudentForms;
use App\Filament\Resources\CourseSections\Students\StudentTable;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManageCourseSectionStudents extends ManageRelatedRecords
{
    protected static string $resource = CourseSectionResource::class;

    protected static string $relationship = 'students';

    protected static ?string $title = 'Sinh viên';

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
                ->schema(StudentForms::create())
                ->using(function (array $data): User {
                    $plainPassword = Str::password(length: 12);

                    $student = User::query()->create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'student_code' => $data['studentId'],
                        'password' => Hash::make($plainPassword),
                        'must_change_password' => true,
                        'password_changed_at' => null,
                        'is_active' => true,
                    ]);

                    $student->syncRoles(['student']);

                    $this->getRecord()->students()->attach($student->id, [
                        'status' => 'enrolled',
                        'enrolled_at' => now(),
                    ]);

                    return $student;
                }),
        ];
    }
}
