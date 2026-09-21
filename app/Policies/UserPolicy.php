<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * A staff photo is shown on appraisals, so anyone who can see one of that person's
     * appraisals (or manages them) may load it — nobody else.
     */
    public function viewPhoto(User $user, User $model): bool
    {
        return $user->isAdmin()
            || $user->id === $model->id
            || $model->line_manager_id === $user->id
            || \App\Models\Appraisal::query()->where('user_id', $model->id)->where('reviewer_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
