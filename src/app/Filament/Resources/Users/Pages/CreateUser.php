<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $generatedPassword = null;

    protected string $selectedRole = 'lecturer';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedRole = $data['role_name'] ?? 'lecturer';
        unset($data['role_name']);

        if (blank($data['password'] ?? null)) {
            $this->generatedPassword = Str::password(length: 10);
            $data['password'] = $this->generatedPassword;
            $data['must_change_password'] = true;
            $data['password_changed_at'] = null;
        } elseif (! ($data['must_change_password'] ?? false)) {
            $data['password_changed_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles([$this->selectedRole]);

        if ($this->generatedPassword) {
            Notification::make()
                ->title('Da tao tai khoan voi mat khau tam')
                ->body("Mat khau tam: {$this->generatedPassword}")
                ->success()
                ->persistent()
                ->send();
        }
    }
}
