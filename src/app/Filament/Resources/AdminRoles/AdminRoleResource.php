<?php

namespace App\Filament\Resources\AdminRoles;

use App\Filament\Resources\AdminRoles\Pages\CreateAdminRole;
use App\Filament\Resources\AdminRoles\Pages\EditAdminRole;
use App\Filament\Resources\AdminRoles\Pages\ListAdminRoles;
use App\Filament\Resources\AdminRoles\Schemas\AdminRoleForm;
use App\Filament\Resources\AdminRoles\Tables\AdminRolesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class AdminRoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string | \UnitEnum | null $navigationGroup = 'Người dùng';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Nhóm quyền admin';

    protected static ?string $modelLabel = 'Nhóm quyền admin';

    protected static ?string $pluralModelLabel = 'Nhóm quyền admin';

    public static function form(Schema $schema): Schema
    {
        return AdminRoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminRolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminRoles::route('/'),
            'create' => CreateAdminRole::route('/create'),
            'edit' => EditAdminRole::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('guard_name', 'admin')
            ->withCount('permissions');
    }

    public static function canViewAny(): bool
    {
        $admin = auth('admin')->user();

        return $admin ? Gate::forUser($admin)->allows('admin.roles.view') : false;
    }

    public static function canCreate(): bool
    {
        $admin = auth('admin')->user();

        return $admin ? Gate::forUser($admin)->allows('admin.roles.create') : false;
    }

    public static function canEdit($record): bool
    {
        $admin = auth('admin')->user();

        return $admin ? Gate::forUser($admin)->allows('admin.roles.update') : false;
    }

    public static function canDelete($record): bool
    {
        $admin = auth('admin')->user();

        if (! $admin || ! Gate::forUser($admin)->allows('admin.roles.delete')) {
            return false;
        }

        return $record->name !== 'root_admin';
    }
}
