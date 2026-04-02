<?php

namespace App\Filament\Resources\AdminRoles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tên vai trò')
                    ->required()
                    ->maxLength(100)
                    ->disabled(fn(?Role $record): bool => $record?->name === 'root_admin')
                    ->unique(
                        table: 'roles',
                        column: 'name',
                        ignoreRecord: true,
                        modifyRuleUsing: fn(Unique $rule): Unique => $rule->where('guard_name', 'admin')
                    )
                    ->dehydrateStateUsing(fn(string $state): string => str($state)->trim()->replace(' ', '_')->lower()->toString())
                    ->helperText('Tên vai trò dùng dạng snake_case. Ví dụ: operations_admin'),

                CheckboxList::make('permission_names')
                    ->label('Chức năng được phép')
                    ->options(fn(): array => self::permissionOptions())
                    ->columns(2)
                    ->bulkToggleable()
                    ->searchable()
                    ->required()
                    ->helperText('Tích chọn các chức năng mà vai trò này được phép thực hiện.'),
            ]);
    }

    private static function permissionOptions(): array
    {
        $moduleLabels = [
            'users' => 'Người dùng',
            'admins' => 'Quản trị viên',
            'roles' => 'Nhóm quyền',
            'settings' => 'Cấu hình hệ thống',
            'reports' => 'Báo cáo',
        ];

        $actionLabels = [
            'view' => 'Xem',
            'create' => 'Tạo',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'block' => 'Khóa/Mở khóa',
            'assign' => 'Gán vai trò',
            'reset-password' => 'Đặt lại mật khẩu',
        ];

        $flat = [];
        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->orderBy('name')
            ->get(['name']);

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = $parts[1] ?? 'other';
            $action = $parts[2] ?? $permission->name;

            $groupLabel = $moduleLabels[$module] ?? str($module)->replace('-', ' ')->headline()->toString();
            $actionLabel = $actionLabels[$action] ?? str($action)->replace('-', ' ')->headline()->toString();

            $flat[$permission->name] = $groupLabel . ' · ' . $actionLabel;
        }

        asort($flat);

        return $flat;
    }
}
