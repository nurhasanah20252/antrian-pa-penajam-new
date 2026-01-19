<?php

namespace App\Policies;

use App\Models\Officer;
use App\Models\User;

class OfficerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Officer $officer): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $officer->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Officer $officer): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $officer->user_id;
    }

    public function delete(User $user, Officer $officer): bool
    {
        return $user->isAdmin();
    }

    public function setAvailability(User $user, Officer $officer): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $officer->user_id;
    }

    public function restore(User $user, Officer $officer): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Officer $officer): bool
    {
        return $user->isAdmin();
    }
}
