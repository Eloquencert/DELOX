<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_workspace_and_project_board(): void
    {
        $user = User::factory()->create();

        $workspaceResponse = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'Studio North',
        ]);
        $workspace = Workspace::query()->where('name', 'Studio North')->firstOrFail();

        $workspaceResponse->assertRedirect(route('workspaces.show', $workspace));
        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->getKey(),
            'user_id' => $user->getKey(),
            'role' => 'owner',
        ]);

        $projectResponse = $this->post(route('projects.store', $workspace), [
            'name' => 'Operations',
            'key' => 'OPS',
            'description' => 'Operations team work.',
        ]);
        $project = Project::query()->where('key', 'OPS')->firstOrFail();

        $projectResponse->assertRedirect(route('projects.board', $project));
        $this->assertDatabaseCount('columns', 4);
        $this->get(route('projects.board', $project))->assertOk();
    }

    public function test_project_creation_rejects_keys_that_are_not_uppercase(): void
    {
        $workspace = Workspace::factory()->create();

        $this->actingAs($workspace->owner)
            ->from(route('workspaces.show', $workspace))
            ->post(route('projects.store', $workspace), [
                'name' => 'Invalid Key Project',
                'key' => 'lower',
            ])
            ->assertSessionHasErrors('key');

        $this->assertDatabaseMissing('projects', ['name' => 'Invalid Key Project']);
    }

    public function test_workspace_members_can_add_existing_users_with_a_role(): void
    {
        $workspace = Workspace::factory()->create();
        $invitee = User::factory()->create();

        $this->actingAs($workspace->owner)
            ->post(route('workspaces.members.store', $workspace), [
                'email' => $invitee->email,
                'role' => 'admin',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->getKey(),
            'user_id' => $invitee->getKey(),
            'role' => 'admin',
        ]);
    }

    public function test_workspace_member_cannot_add_other_members(): void
    {
        $workspace = Workspace::factory()->create();
        $member = User::factory()->create();
        $invitee = User::factory()->create();
        $workspace->users()->attach($member, ['role' => 'member']);

        $this->actingAs($member)
            ->post(route('workspaces.members.store', $workspace), [
                'email' => $invitee->email,
                'role' => 'member',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('workspace_user', [
            'workspace_id' => $workspace->getKey(),
            'user_id' => $invitee->getKey(),
        ]);
    }

    public function test_duplicate_workspace_member_is_rejected(): void
    {
        $workspace = Workspace::factory()->create();

        $this->actingAs($workspace->owner)
            ->from(route('workspaces.members', $workspace))
            ->post(route('workspaces.members.store', $workspace), [
                'email' => $workspace->owner->email,
                'role' => 'member',
            ])
            ->assertSessionHasErrors('email');
    }
}
