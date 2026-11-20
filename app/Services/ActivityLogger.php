<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(
        User $actor,
        Project $project,
        string $action,
        string $description,
        ?Task $task = null,
        array $properties = [],
    ): Activity {
        return $project->activities()->create([
            'task_id' => $task?->getKey(),
            'user_id' => $actor->getKey(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
        ]);
    }
}
