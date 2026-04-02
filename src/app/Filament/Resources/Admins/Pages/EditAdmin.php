<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use App\Models\Admin;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected string $selectedRole = 'system_admin';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_name'] = $this->record->roles()
            ->where('guard_name', 'admin')
            ->value('name');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $actor = auth('admin')->user();
        $currentRole = (string) ($this->record->roles()
            ->where('guard_name', 'admin')
            ->value('name') ?? 'system_admin');

        $selectedRole = (string) ($data['role_name'] ?? $currentRole);

        if (! Role::query()->where('guard_name', 'admin')->where('name', $selectedRole)->exists()) {
            throw ValidationException::withMessages([
                'role_name' => 'Vai trò được chọn không hợp lệ.',
            ]);
        }

        if (! $actor instanceof Admin || ! $this->canAssignRole($actor, $selectedRole, $currentRole)) {
            throw ValidationException::withMessages([
                'role_name' => 'Bạn không có quyền thay đổi vai trò này.',
            ]);
        }

        $this->selectedRole = $selectedRole;
        unset($data['role_name']);
        unset($data['is_super_admin']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['must_change_password'] = false;
            $data['password_changed_at'] = now();
        }

        $data['is_super_admin'] = $this->selectedRole === 'root_admin';

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncRoles([$this->selectedRole]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    private function canAssignRole(Admin $actor, string $newRole, string $currentRole): bool
    {
        if (! $actor->is_active) {
            return false;
        }

        if ($newRole === $currentRole) {
            return true;
        }

        if (! $actor->can('admin.roles.assign')) {
            return false;
        }

        if ($newRole === 'root_admin' && ! $actor->is_super_admin) {
            return false;
        }

        if ($this->record->id === $actor->id && $currentRole === 'root_admin' && $newRole !== 'root_admin') {
            return false;
        }

        return true;
    }
}
