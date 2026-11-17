<?php

declare(strict_types=1);

use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

Broadcast::channel('project.{projectId}', function ($user, int $projectId): bool {
    return Project::query()
        ->whereKey($projectId)
        ->whereHas('workspace.users', fn ($query) => $query->whereKey($user->getKey()))
        ->exists();
});
