<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdminForm
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
                    ->label('Vai trò quản trị viên')
                    ->options([
                        'root_admin' => 'Root Admin',
                        'system_admin' => 'System Admin',
                    ])
                    ->default('system_admin')
                    ->required()
                    ->dehydrated(),

                TextInput::make('password')
                    ->label('Mật khẩu')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),

                TextInput::make('password_confirmation')
                    ->label('Nhập lại mật khẩu')
                    ->password()
                    ->revealable()
                    ->same('password')
                    ->dehydrated(false),

                Toggle::make('is_active')
                    ->label('Tài khoản hoạt động')
                    ->default(true)
                    ->required(),

                Toggle::make('is_super_admin')
                    ->label('Có quyền super admin')
                    ->default(false),
            ]);
    }
}
