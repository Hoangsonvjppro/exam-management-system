<?php

namespace App\Filament\Resources\AdminRoles\Pages;

use App\Filament\Resources\AdminRoles\AdminRoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminRole extends CreateRecord
{
    protected static string $resource = AdminRoleResource::class;

    /** @var array<int, string> */
    protected array $selectedPermissions = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedPermissions = array_values($data['permission_names'] ?? []);
        unset($data['permission_names']);

        $data['guard_name'] = 'admin';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPermissions($this->selectedPermissions);
    }
}
