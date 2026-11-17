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
        return $project->workspace->users()->whereKey($user->getKey())->exists();
    }

    public function update(User $user, Column $column): bool
    {
        return $this->isMember($user, $column);
    }

    public function move(User $user, Column $column): bool
    {
        return $this->isMember($user, $column);
    }

    public function delete(User $user, Column $column): bool
    {
        return $this->isMember($user, $column);
    }

    private function isMember(User $user, Column $column): bool
    {
        return $column->project->workspace->users()->whereKey($user->getKey())->exists();
    }
}
