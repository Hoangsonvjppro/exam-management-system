<?php

namespace App\Filament\Resources\Admins;

use App\Filament\Resources\Admins\Pages\CreateAdmin;
use App\Filament\Resources\Admins\Pages\EditAdmin;
use App\Filament\Resources\Admins\Pages\ListAdmins;
use App\Filament\Resources\Admins\Schemas\AdminForm;
use App\Filament\Resources\Admins\Tables\AdminsTable;
use App\Models\Admin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

     protected static string | \UnitEnum | null $navigationGroup = 'Người dùng';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Quản trị viên';

    protected static ?string $modelLabel = 'Quản trị viên';

    protected static ?string $pluralModelLabel = 'Quản trị viên';

    public static function form(Schema $schema): Schema
    {
        return AdminForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminsTable::configure($table);
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
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $admin = auth('admin')->user();

        return $admin ? Gate::forUser($admin)->allows('admin.admins.view') : false;
    }

    public static function canCreate(): bool
    {
        $admin = auth('admin')->user();

        return $admin ? Gate::forUser($admin)->allows('admin.admins.create') : false;
    }

    public static function canEdit($record): bool
    {
        $admin = auth('admin')->user();

        return $admin ? Gate::forUser($admin)->allows('admin.admins.update') : false;
    }
}
