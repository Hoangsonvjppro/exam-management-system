<?php

namespace App\Policies;

use App\Models\QuestionType;
use App\Models\User;

class QuestionTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['lecturer', 'teaching_assistant']);
    }

    public function view(User $user, QuestionType $questionType): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, QuestionType $questionType): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, QuestionType $questionType): bool
    {
        return $this->viewAny($user);
    }
}
