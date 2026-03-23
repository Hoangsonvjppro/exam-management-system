<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['lecturer', 'teaching_assistant']);
    }

    public function view(User $user, Question $question): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Question $question): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Question $question): bool
    {
        return $this->viewAny($user);
    }
}
