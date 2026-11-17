<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceMemberRequest;
use App\Http\Requests\UpdateWorkspaceMemberRequest;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;

class WorkspaceMemberController extends Controller
{
    public function store(StoreWorkspaceMemberRequest $request, Workspace $workspace): RedirectResponse
    {
        $member = User::query()->where('email', $request->validated('email'))->firstOrFail();

        $workspace->users()->attach($member, ['role' => $request->validated('role')]);

        return back()->with('success', "{$member->name} joined the workspace.");
    }

    public function update(
        UpdateWorkspaceMemberRequest $request,
        Workspace $workspace,
        User $user,
    ): RedirectResponse {
        abort_if((int) $workspace->owner_id === (int) $user->getKey(), 422);

        $workspace->users()->updateExistingPivot($user->getKey(), [
            'role' => $request->validated('role'),
        ]);

        return back()->with('success', 'Workspace role updated.');
    }

    public function destroy(Workspace $workspace, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $workspace);

        abort_if((int) $workspace->owner_id === (int) $user->getKey(), 422);

        $workspace->users()->detach($user);

        return back()->with('success', 'Workspace member removed.');
    }
}
