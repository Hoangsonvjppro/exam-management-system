<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected string $selectedRole = 'system_admin';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedRole = $data['role_name'] ?? 'system_admin';
        unset($data['role_name']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles([$this->selectedRole]);
    }
}
