<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use App\Models\Admin;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected string $selectedRole = 'system_admin';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $selectedRole = (string) ($data['role_name'] ?? 'system_admin');
        $actor = auth('admin')->user();

        if (! Role::query()->where('guard_name', 'admin')->where('name', $selectedRole)->exists()) {
            throw ValidationException::withMessages([
                'role_name' => 'Vai trò được chọn không hợp lệ.',
            ]);
        }

        if (! $actor instanceof Admin || ! $this->canAssignRole($actor, $selectedRole)) {
            throw ValidationException::withMessages([
                'role_name' => 'Bạn không có quyền gán vai trò này.',
            ]);
        }

        $this->selectedRole = $selectedRole;
        unset($data['role_name']);
        unset($data['is_super_admin']);

        $data['is_super_admin'] = $this->selectedRole === 'root_admin';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles([$this->selectedRole]);
    }

    private function canAssignRole(Admin $actor, string $roleName): bool
    {
        if (! $actor->is_active) {
            return false;
        }

        if (! $actor->can('admin.roles.assign')) {
            return false;
        }

        if ($roleName === 'root_admin' && ! $actor->is_super_admin) {
            return false;
        }

        return true;
    }
}
