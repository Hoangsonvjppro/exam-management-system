<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected string $selectedRole = 'student';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_name'] = $this->record->roles()
            ->where('guard_name', 'web')
            ->value('name');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedRole = $data['role_name'] ?? 'student';
        unset($data['role_name']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } elseif (! ($data['must_change_password'] ?? false)) {
            $data['password_changed_at'] = now();
        } else {
            $data['password_changed_at'] = null;
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
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
