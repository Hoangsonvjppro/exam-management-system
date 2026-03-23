<?php

namespace App\Policies;

use App\Models\Difficulty;
use App\Models\User;

class DifficultyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['lecturer', 'teaching_assistant']);
    }

    public function view(User $user, Difficulty $difficulty): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Difficulty $difficulty): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Difficulty $difficulty): bool
    {
        return $this->viewAny($user);
    }
}
