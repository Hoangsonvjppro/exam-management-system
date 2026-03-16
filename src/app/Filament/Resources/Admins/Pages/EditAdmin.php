<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

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
        $this->selectedRole = $data['role_name'] ?? 'system_admin';
        unset($data['role_name']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['must_change_password'] = false;
            $data['password_changed_at'] = now();
        }

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
}
