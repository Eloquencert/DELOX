<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $project->workspace->users()->whereKey($user->getKey())->exists();
    }

    public function create(User $user, Workspace $workspace): bool
    {
        return $this->canManageWorkspaceProjects($user, $workspace);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->canManageProject($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->canManageProject($user, $project);
    }

    private function canManageProject(User $user, Project $project): bool
    {
        return $this->canManageWorkspaceProjects($user, $project->workspace);
    }

    private function canManageWorkspaceProjects(User $user, Workspace $workspace): bool
    {
        return $workspace->users()
            ->whereKey($user->getKey())
            ->wherePivotIn('role', ['owner', 'admin'])
            ->exists();
    }
}
