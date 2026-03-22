<?php

namespace App\Services;

use App\Models\User;

class UserStateService
{
    public function syncStudentRole(User $user): void
    {
        if ($user->hasRole('lecturer')) {
            if ($user->hasRole('student')) {
                $user->removeRole('student');
            }

            return;
        }

        $hasEnrollment = $user->enrolledSections()->exists();

        if ($hasEnrollment && ! $user->hasRole('student')) {
            $user->assignRole('student');
        }

        if (! $hasEnrollment && $user->hasRole('student')) {
            $user->removeRole('student');
        }
    }

    public function determineHomeRouteName(User $user): string
    {
        $this->syncStudentRole($user);

        if ($user->hasRole('lecturer')) {
            return 'lecturer.dashboard';
        }

        if ($user->hasRole('student')) {
            return 'student.dashboard';
        }

        // Authenticated user without specific role stays on landing page.
        return 'landing';
    }
}
