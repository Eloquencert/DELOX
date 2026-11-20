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
        return $this->canManageProject($user, $project);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->canManageProject($user, $task->project);
    }

    public function move(User $user, Task $task): bool
    {
        return $this->canManageProject($user, $task->project);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->canManageProject($user, $task->project);
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->canManageProject($user, $task->project);
    }

    private function canManageProject(User $user, Project $project): bool
    {
        return $project->workspace->users()
            ->whereKey($user->getKey())
            ->wherePivotIn('role', ['owner', 'admin'])
            ->exists();
    }

    private function isMember(User $user, Task $task): bool
    {
        return $task->project->workspace->users()->whereKey($user->getKey())->exists();
    }
}
