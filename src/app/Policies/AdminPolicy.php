<?php

namespace App\Policies;

use App\Models\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active && $admin->checkPermissionTo('admin.admins.view', 'admin');
    }

    public function view(Admin $admin, Admin $target): bool
    {
        if (! $this->viewAny($admin)) {
            return false;
        }

        if ($target->is_super_admin && ! $admin->is_super_admin) {
            return false;
        }

        return true;
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active && $admin->checkPermissionTo('admin.admins.create', 'admin');
    }

    public function update(Admin $admin, Admin $target): bool
    {
        if (! ($admin->is_active && $admin->checkPermissionTo('admin.admins.update', 'admin'))) {
            return false;
        }

        if ($target->is_super_admin && ! $admin->is_super_admin) {
            return false;
        }

        return true;
    }

    public function delete(Admin $admin, Admin $target): bool
    {
        return false;
    }

    public function restore(Admin $admin, Admin $target): bool
    {
        return false;
    }

    public function forceDelete(Admin $admin, Admin $target): bool
    {
        return false;
    }
}
