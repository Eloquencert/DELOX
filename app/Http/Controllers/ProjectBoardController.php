<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectBoardController extends Controller
{
    public function show(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load([
            'workspace',
            'columns.tasks' => fn ($query) => $query
                ->with(['creator:id,name', 'assignee:id,name', 'comments.user:id,name'])
                ->orderBy('position'),
        ]);

        return Inertia::render('Projects/Board', [
            'project' => $project->only(['id', 'name', 'key', 'description', 'workspace_id']),
            'columns' => $project->columns,
            'members' => $project->workspace->users()->orderBy('name')->get(['users.id', 'users.name']),
            'canManageProjects' => $request->user()->can('update', $project),
        ]);
    }
}
