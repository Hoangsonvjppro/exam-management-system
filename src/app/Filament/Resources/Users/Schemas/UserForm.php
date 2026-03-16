<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Ho ten')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('role_name')
                    ->label('Vai tro')
                    ->options([
                        'lecturer' => 'Giang vien',
                        'student' => 'Sinh vien',
                    ])
                    ->default('lecturer')
                    ->required()
                    ->dehydrated(),

                TextInput::make('lecturer_code')
                    ->label('Ma giang vien')
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),

                TextInput::make('student_code')
                    ->label('MSSV')
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),

                TextInput::make('phone')
                    ->label('So dien thoai')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('department')
                    ->label('Khoa / Bo mon')
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('Mat khau')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Bo trong de tu sinh mat khau tam cho giang vien.'),

                TextInput::make('password_confirmation')
                    ->label('Nhap lai mat khau')
                    ->password()
                    ->revealable()
                    ->same('password')
                    ->dehydrated(false),

                Toggle::make('is_active')
                    ->label('Tai khoan hoat dong')
                    ->default(true)
                    ->required(),

                Toggle::make('must_change_password')
                    ->label('Buoc doi mat khau khi dang nhap')
                    ->default(false),
            ]);
    }
}
