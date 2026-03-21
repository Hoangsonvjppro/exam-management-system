<?php

namespace App\Filament\Resources\CourseSections\Students;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class StudentForms
{
    public static function create(): array
    {
        return [
            TextInput::make('name')
                ->label('Họ và tên')
                ->required()
                ->maxLength(255),
            TextInput::make('studentId')
                ->label('Mã sinh viên')
                ->required()
                ->maxLength(20)
                ->unique(User::class, 'student_code'),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(User::class, 'email'),
            TextInput::make('phone')
                ->label('Số điện thoại')
                ->maxLength(20),
        ];
    }

    public static function edit(): array
    {
        return [
            TextInput::make('name')
                ->label('Họ và tên')
                ->required()
                ->maxLength(255),
            TextInput::make('student_code')
                ->label('Mã sinh viên')
                ->required()
                ->maxLength(20)
                ->unique(User::class, 'student_code', ignoreRecord: true),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(User::class, 'email', ignoreRecord: true),
            TextInput::make('phone')
                ->label('Số điện thoại')
                ->maxLength(20),
            Select::make('enrollment_status')
                ->label('Trạng thái')
                ->options([
                    'enrolled' => 'Đang học',
                    'dropped' => 'Đã rút',
                    'completed' => 'Hoàn thành',
                ])
                ->required(),
        ];
    }
}
