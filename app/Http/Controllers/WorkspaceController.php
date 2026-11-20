<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceRequest;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public function show(Request $request, Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);

        /** @var User $user */
        $user = $request->user();
        $workspaces = $user->workspaces()
            ->withCount('users')
            ->with([
                'users:id,name',
                'projects' => fn ($query) => $query->withCount('tasks'),
            ])
            ->get();

        return Inertia::render('Dashboard', [
            'workspaces' => $workspaces,
            'currentWorkspace' => $workspaces->firstWhere('id', $workspace->getKey()),
            'canDeleteWorkspace' => $request->user()->can('delete', $workspace),
            'canManageMembers' => $request->user()->can('manageMembers', $workspace),
            'canManageProjects' => $request->user()->can('create', [Project::class, $workspace]),
        ]);
    }

    public function store(StoreWorkspaceRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $name = $request->validated('name');

        $workspace = Workspace::query()->create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.$user->getKey().'-'.Str::lower(Str::random(6)),
            'owner_id' => $user->getKey(),
        ]);

        return to_route('workspaces.show', $workspace);
    }

    public function destroy(Workspace $workspace): RedirectResponse
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return to_route('dashboard')->with('success', 'Workspace deleted.');
    }

    public function members(Request $request, Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);

        return Inertia::render('Workspaces/Members', [
            'workspace' => $workspace,
            'members' => $workspace->users()
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email']),
            'canManageMembers' => Gate::forUser($request->user())->allows('manageMembers', $workspace),
        ]);
    }
}
