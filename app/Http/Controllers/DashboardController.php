<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $workspaces = $user->workspaces()
            ->withCount('users')
            ->with([
                'users:id,name',
                'projects' => fn ($query) => $query->withCount('tasks'),
            ])
            ->get();
        $currentWorkspace = $workspaces->first();

        return Inertia::render('Dashboard', [
            'workspaces' => $workspaces,
            'currentWorkspace' => $currentWorkspace,
            'canDeleteWorkspace' => $currentWorkspace !== null
                && $user->can('delete', $currentWorkspace),
            'canManageMembers' => $currentWorkspace !== null
                && $user->can('manageMembers', $currentWorkspace),
            'canManageProjects' => $currentWorkspace !== null
                && $user->can('create', [Project::class, $currentWorkspace]),
        ]);
    }
}
