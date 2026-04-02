<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class UserStateService
{
    private function roleTablesReady(): bool
    {
        return Schema::hasTable('roles') && Schema::hasTable('model_has_roles');
    }

    public function syncStudentRole(User $user): void
    {
        if (! $this->roleTablesReady()) {
            return;
        }

        try {
            if ($user->hasRole('lecturer')) {
                if ($user->hasRole('student')) {
                    $user->removeRole('student');
                }

                return;
            }

            // Always ensure non-lecturers have the 'student' role.
            if (! $user->hasRole('student')) {
                $user->assignRole('student');
            }
        } catch (QueryException) {
            // Skip role-sync if schema is not ready.
        }
    }

    public function determineHomeRouteName(User $user): string
    {
        $this->syncStudentRole($user);

        if (! $this->roleTablesReady()) {
            return 'landing';
        }

        try {
            if ($user->hasRole('lecturer')) {
                return 'lecturer.dashboard';
            }

            // Authenticated students (or users with no specific role) go to dashboard.
            return 'student.dashboard';
        } catch (QueryException) {
            return 'landing';
        }
    }
}
