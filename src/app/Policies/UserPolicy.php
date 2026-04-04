<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;

class UserPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active && $admin->checkPermissionTo('admin.users.view', 'admin');
    }

    public function view(Admin $admin, User $user): bool
    {
        return $this->viewAny($admin);
    }

    
    public function create(Admin $admin): bool
    {
        return $admin->is_active && $admin->checkPermissionTo('admin.users.create', 'admin');
    }

    public function update(Admin $admin, User $user): bool
    {
        return $admin->is_active && $admin->checkPermissionTo('admin.users.update', 'admin');
    }

    public function delete(Admin $admin, User $user): bool
    {
        return $admin->is_active && $admin->checkPermissionTo('admin.users.delete', 'admin');
    }

    public function restore(Admin $admin, User $user): bool
    {
        return false;
    }

    public function forceDelete(Admin $admin, User $user): bool
    {
        return false;
    }
}
