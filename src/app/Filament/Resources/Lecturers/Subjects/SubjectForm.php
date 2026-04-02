<?php

namespace App\Filament\Resources\Lecturers\Subjects;

use App\Models\Subject;
use Filament\Forms\Components\Select;

class SubjectForm
{
    public static function make($lecturerId = null): array
    {
        return [
            Select::make('subject_id')
                ->label('Môn học')
                ->searchable()
                ->preload()
                ->required()
                ->options(function () use ($lecturerId) {
                    return Subject::query()
                        ->when($lecturerId, function ($q) use ($lecturerId) {
                            $q->whereDoesntHave('lecturers', function ($q) use ($lecturerId) {
                                $q->where('users.id', $lecturerId);
                            });
                        })
                        ->pluck('name', 'id');
                }),
        ];
    }
}
