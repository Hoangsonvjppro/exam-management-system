<?php

namespace App\Filament\Resources\AdminRoles\Pages;

use App\Filament\Resources\AdminRoles\AdminRoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditAdminRole extends EditRecord
{
    protected static string $resource = AdminRoleResource::class;

    /** @var array<int, string> */
    protected array $selectedPermissions = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['permission_names'] = $this->record->permissions()->pluck('name')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->name === 'root_admin') {
            throw ValidationException::withMessages([
                'name' => 'Vai trò root_admin là vai trò hệ thống và không thể chỉnh sửa.',
            ]);
        }

        $this->selectedPermissions = array_values($data['permission_names'] ?? []);
        unset($data['permission_names']);

        $data['guard_name'] = 'admin';

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPermissions($this->selectedPermissions);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn(): bool => $this->record->name !== 'root_admin'),
        ];
    }
}
