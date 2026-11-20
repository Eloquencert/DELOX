<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Services\ActivityLogger;
use App\Services\FractionalIndexingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function update(
        UpdateProjectRequest $request,
        Project $project,
        ActivityLogger $activityLogger,
    ): RedirectResponse {
        $data = $request->validated();
        $changedFields = [];

        foreach ($data as $field => $value) {
            $originalValue = $project->getRawOriginal($field);
            $normalizedOriginal = $originalValue === null ? null : (string) $originalValue;
            $normalizedValue = $value === null ? null : (string) $value;

            if ($normalizedOriginal !== $normalizedValue) {
                $changedFields[] = $field;
            }
        }

        $project->update($data);

        if ($changedFields !== []) {
            /** @var User $user */
            $user = $request->user();
            $activityLogger->record(
                $user,
                $project,
                'project.updated',
                'Updated project details: '.implode(', ', $changedFields).'.',
                properties: ['fields' => $changedFields],
            );
        }

        return to_route('projects.board', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $workspace = $project->workspace;
        $project->delete();

        return to_route('workspaces.show', $workspace)
            ->with('success', 'Project deleted.');
    }

    public function store(
        StoreProjectRequest $request,
        Workspace $workspace,
        FractionalIndexingService $indexing,
        ActivityLogger $activityLogger,
    ): RedirectResponse {
        $data = $request->validated();

        $project = DB::transaction(function () use ($data, $workspace, $indexing): Project {
            $project = $workspace->projects()->create($data);
            $previousPosition = null;

            foreach (['Backlog', 'In Progress', 'In Review', 'Done'] as $name) {
                $position = $indexing->generateKeyBetween($previousPosition, null);
                $project->columns()->create(['name' => $name, 'position' => $position]);
                $previousPosition = $position;
            }

            return $project;
        });

        /** @var User $user */
        $user = $request->user();
        $activityLogger->record(
            $user,
            $project,
            'project.created',
            "Created project {$project->name}.",
        );

        return to_route('projects.board', $project);
    }
}
