<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->workspaces()->exists();
    }

    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->users()->whereKey($user->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Workspace $workspace): bool
    {
        return (int) $workspace->owner_id === (int) $user->getKey();
    }

    public function delete(User $user, Workspace $workspace): bool
    {
        return (int) $workspace->owner_id === (int) $user->getKey();
    }

    public function manageMembers(User $user, Workspace $workspace): bool
    {
        return (int) $workspace->owner_id === (int) $user->getKey();
    }
}
