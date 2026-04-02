<?php

namespace App\Filament\Support;

use Illuminate\Support\Facades\Gate;

trait HasAdminCrudPermissions
{
    abstract protected static function getAdminPermissionModule(): string;

    protected static function canForAction(string $action): bool
    {
        $admin = auth('admin')->user();

        if (! $admin) {
            return false;
        }

        return Gate::forUser($admin)->allows('admin.' . static::getAdminPermissionModule() . '.' . $action);
    }

    public static function canViewAny(): bool
    {
        return static::canForAction('view');
    }

    public static function canCreate(): bool
    {
        return static::canForAction('create');
    }

    public static function canEdit($record): bool
    {
        return static::canForAction('update');
    }

    public static function canDelete($record): bool
    {
        return static::canForAction('delete');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }
}
