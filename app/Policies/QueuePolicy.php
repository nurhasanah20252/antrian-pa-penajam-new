<?php

namespace App\Policies;

use App\Models\Queue;
use App\Models\User;
use App\UserRole;

class QueuePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Queue $queue): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isOfficer() && $user->officer?->service_id === $queue->service_id) {
            return true;
        }

        return $queue->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Queue $queue): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isOfficer() && $user->officer?->service_id === $queue->service_id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Queue $queue): bool
    {
        return $user->isAdmin();
    }

    public function call(User $user, Queue $queue): bool
    {
        if (! $user->isOfficer()) {
            return false;
        }

        $officer = $user->officer;

        return $officer
            && $officer->service_id === $queue->service_id
            && $officer->canAcceptQueue();
    }

    public function process(User $user, Queue $queue): bool
    {
        if (! $user->isOfficer()) {
            return false;
        }

        return $user->officer?->id === $queue->officer_id;
    }

    public function complete(User $user, Queue $queue): bool
    {
        return $this->process($user, $queue);
    }

    public function skip(User $user, Queue $queue): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isOfficer()) {
            return false;
        }

        return $user->officer?->service_id === $queue->service_id;
    }

    public function cancel(User $user, Queue $queue): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $queue->user_id === $user->id && $queue->isWaiting();
    }

    public function transfer(User $user, Queue $queue): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isOfficer()) {
            return false;
        }

        return $user->officer?->service_id === $queue->service_id && $queue->status->isActive();
    }

    public function restore(User $user, Queue $queue): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Queue $queue): bool
    {
        return $user->isAdmin();
    }
}
