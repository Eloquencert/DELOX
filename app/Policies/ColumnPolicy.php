<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Column;
use App\Models\Project;
use App\Models\User;

class ColumnPolicy
{
    public function view(User $user, Column $column): bool
    {
        return $this->isMember($user, $column);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->canManageProject($user, $project);
    }

    public function update(User $user, Column $column): bool
    {
        return $this->canManageProject($user, $column->project);
    }

    public function move(User $user, Column $column): bool
    {
        return $this->canManageProject($user, $column->project);
    }

    public function delete(User $user, Column $column): bool
    {
        return $this->canManageProject($user, $column->project);
    }

    private function canManageProject(User $user, Project $project): bool
    {
        return $project->workspace->users()
            ->whereKey($user->getKey())
            ->wherePivotIn('role', ['owner', 'admin'])
            ->exists();
    }

    private function isMember(User $user, Column $column): bool
    {
        return $column->project->workspace->users()->whereKey($user->getKey())->exists();
    }
}
