<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Họ tên')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('role_name')
                    ->label('Vai trò')
                    ->options([
                        'lecturer' => 'Giảng viên',
                        'student'  => 'Sinh viên',
                    ])
                    ->default('lecturer')
                    ->required()
                    ->live()
                    ->dehydrated(),

                // Lecturer-only fields
                TextInput::make('lecturer_code')
                    ->label('Mã giảng viên')
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->visible(fn (Get $get): bool => $get('role_name') === 'lecturer'),

                TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->tel()
                    ->maxLength(20)
                    ->visible(fn (Get $get): bool => $get('role_name') === 'lecturer'),

                TextInput::make('department')
                    ->label('Khoa / Bộ môn')
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => $get('role_name') === 'lecturer'),

                TextInput::make('password')
                    ->label('Mật khẩu')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Bỏ trống để tự sinh mật khẩu tạm cho giảng viên.')
                    ->visible(fn (Get $get): bool => $get('role_name') === 'lecturer'),

                TextInput::make('password_confirmation')
                    ->label('Nhập lại mật khẩu')
                    ->password()
                    ->revealable()
                    ->same('password')
                    ->dehydrated(false)
                    ->visible(fn (Get $get): bool => $get('role_name') === 'lecturer'),

                Toggle::make('must_change_password')
                    ->label('Bắt buộc đổi mật khẩu khi đăng nhập')
                    ->default(false)
                    ->visible(fn (Get $get): bool => $get('role_name') === 'lecturer'),

                // Student-only fields
                TextInput::make('student_code')
                    ->label('MSSV')
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->visible(fn (Get $get): bool => $get('role_name') === 'student'),

                // Shared fields
                Toggle::make('is_active')
                    ->label('Tài khoản hoạt động')
                    ->default(true)
                    ->required(),
            ]);
    }
}

