<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    public function create(User $user, Project $project): bool
    {
        return $project->workspace->users()->whereKey($user->getKey())->exists();
    }

    public function update(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    public function move(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    private function isMember(User $user, Task $task): bool
    {
        return $task->project->workspace->users()->whereKey($user->getKey())->exists();
    }
}
