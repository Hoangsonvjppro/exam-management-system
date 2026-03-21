<?php

namespace App\Filament\Resources\CourseSections\Students;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentTable
{
    public static function configure(Table $table, object $page): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->role('student'))
            ->columns([
                TextColumn::make('name')
                    ->label('Họ và tên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student_code')
                    ->label('Mã sinh viên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable(),
                TextColumn::make('pivot.status')
                    ->label('Trạng thái')
                    ->badge()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            "CASE course_section_students.status
                                WHEN 'enrolled' THEN 1
                                WHEN 'completed' THEN 2
                                WHEN 'dropped' THEN 3
                                ELSE 4
                            END {$direction}"
                        );
                    }),
            ])
            ->filters([
                SelectFilter::make('pivot_status')
                    ->label('Trạng thái')
                    ->options([
                        'enrolled' => 'Đang học',
                        'dropped' => 'Đã rút',
                        'completed' => 'Hoàn thành',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $q): Builder => $q->where('course_section_students.status', $data['value'])
                    )),
            ])
            ->recordAction('edit')
            ->recordActions([
                EditAction::make()
                    ->label('Sửa')
                    ->fillForm(function (User $record): array {
                        return [
                            'name' => $record->name,
                            'student_code' => $record->student_code,
                            'email' => $record->email,
                            'phone' => $record->phone,
                            'enrollment_status' => $record->pivot?->status ?? 'enrolled',
                        ];
                    })
                    ->schema(StudentForms::edit())
                    ->using(function (User $record, array $data) use ($page): User {
                        $status = $data['enrollment_status'];
                        unset($data['enrollment_status']);

                        $record->update($data);

                        $page->getRecord()->students()->updateExistingPivot($record->id, [
                            'status' => $status,
                        ]);

                        return $record;
                    }),
                DeleteAction::make()->label('Xóa'),
            ]);
    }
}
