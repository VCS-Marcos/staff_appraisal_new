<?php

namespace App\Policies;

use App\Models\AppraisalCycle;
use App\Models\User;

class AppraisalCyclePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AppraisalCycle $cycle): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AppraisalCycle $cycle): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, AppraisalCycle $cycle): bool
    {
        return $user->isAdmin();
    }
}
