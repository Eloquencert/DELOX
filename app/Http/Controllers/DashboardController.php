<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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

        return Inertia::render('Dashboard', [
            'workspaces' => $workspaces,
            'currentWorkspace' => $workspaces->first(),
        ]);
    }
}
