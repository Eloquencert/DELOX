<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Workspace;
use App\Services\FractionalIndexingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

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

        return to_route('projects.board', $project);
    }
}
