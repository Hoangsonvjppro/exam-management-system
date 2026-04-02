<?php

namespace App\Filament\Resources\Admins\Schemas;

use App\Models\Admin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

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
                    ->options(function (): array {
                        $roles = Role::query()
                            ->where('guard_name', 'admin')
                            ->orderBy('name');

                        $actor = auth('admin')->user();
                        if ($actor instanceof Admin && ! $actor->is_super_admin) {
                            $roles->where('name', '!=', 'root_admin');
                        }

                        return $roles
                            ->pluck('name')
                            ->mapWithKeys(fn(string $name): array => [$name => str($name)->replace('_', ' ')->title()->toString()])
                            ->all();
                    })
                    ->default('system_admin')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->dehydrated(),

                TextInput::make('password')
                    ->label('Mật khẩu')
                    ->password()
                    ->revealable()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(fn(?string $state): bool => filled($state)),

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
            ]);
    }
}
