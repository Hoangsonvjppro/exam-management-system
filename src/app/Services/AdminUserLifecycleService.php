<?php

namespace App\Services;

use App\Models\User;
use DomainException;
use Illuminate\Support\Str;

class AdminUserLifecycleService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function toggleActive(User $user): bool
    {
        $newState = ! $user->is_active;

        $user->update([
            'is_active' => $newState,
        ]);

        $this->auditLogService->logAdminAction('users.toggle_active', $user, [
            'is_active' => $newState,
        ]);

        return $newState;
    }

    public function resetLecturerPassword(User $user): string
    {
        if (! $user->hasRole('lecturer')) {
            throw new DomainException('Only lecturer accounts can be reset by this workflow.');
        }

        $temporaryPassword = Str::password(length: 10);

        $user->update([
            'password' => $temporaryPassword,
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $this->auditLogService->logAdminAction('users.reset_password', $user, [
            'must_change_password' => true,
        ]);

        return $temporaryPassword;
    }
}
