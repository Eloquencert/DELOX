<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_admin_can_create_and_update_projects_while_member_cannot(): void
    {
        $workspace = Workspace::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $workspace->users()->attach($admin, ['role' => 'admin']);
        $workspace->users()->attach($member, ['role' => 'member']);

        $this->actingAs($admin)
            ->post(route('projects.store', $workspace), [
                'name' => 'Operations',
                'key' => 'OPS',
                'description' => 'Operations team work.',
            ])
            ->assertRedirect();
        $project = Project::query()->where('key', 'OPS')->firstOrFail();

        $this->patch(route('projects.update', $project), [
            'name' => 'Operations Hub',
            'key' => 'OPS',
            'description' => 'Updated project details.',
        ])->assertRedirect(route('projects.board', $project));

        $this->assertDatabaseHas('projects', [
            'id' => $project->getKey(),
            'name' => 'Operations Hub',
            'description' => 'Updated project details.',
        ]);

        $this->actingAs($member)
            ->post(route('projects.store', $workspace), [
                'name' => 'Member Project',
                'key' => 'MEM',
            ])
            ->assertForbidden();

        $this->patch(route('projects.update', $project), [
            'name' => 'Unauthorized update',
            'key' => 'OPS',
        ])->assertForbidden();

        $this->assertDatabaseMissing('projects', ['name' => 'Member Project']);
        $this->assertDatabaseMissing('projects', ['name' => 'Unauthorized update']);
    }

    public function test_admin_can_delete_projects_and_project_data_is_removed(): void
    {
        $workspace = Workspace::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $workspace->users()->attach($admin, ['role' => 'admin']);
        $workspace->users()->attach($member, ['role' => 'member']);
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create();

        $this->actingAs($member)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('workspaces.show', $workspace));

        $this->assertDatabaseMissing('projects', ['id' => $project->getKey()]);
        $this->assertDatabaseMissing('columns', ['id' => $column->getKey()]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->getKey()]);
    }

    public function test_only_owner_can_delete_workspace_and_its_projects(): void
    {
        $workspace = Workspace::factory()->create();
        $admin = User::factory()->create();
        $workspace->users()->attach($admin, ['role' => 'admin']);
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create();

        $this->actingAs($admin)
            ->delete(route('workspaces.destroy', $workspace))
            ->assertForbidden();
        $this->assertModelExists($workspace);

        $this->actingAs($workspace->owner)
            ->delete(route('workspaces.destroy', $workspace))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('workspaces', ['id' => $workspace->getKey()]);
        $this->assertDatabaseMissing('projects', ['id' => $project->getKey()]);
        $this->assertDatabaseMissing('columns', ['id' => $column->getKey()]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->getKey()]);
    }

    public function test_workspace_admin_cannot_manage_members(): void
    {
        $workspace = Workspace::factory()->create();
        $admin = User::factory()->create();
        $invitee = User::factory()->create();
        $workspace->users()->attach($admin, ['role' => 'admin']);

        $this->actingAs($admin)
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

    public function test_workspace_page_exposes_role_based_management_capabilities(): void
    {
        $workspace = Workspace::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $workspace->users()->attach($admin, ['role' => 'admin']);
        $workspace->users()->attach($member, ['role' => 'member']);

        $this->actingAs($workspace->owner)
            ->get(route('workspaces.show', $workspace))
            ->assertInertia(fn (Assert $page) => $page
                ->where('canDeleteWorkspace', true)
                ->where('canManageMembers', true)
                ->where('canManageProjects', true));

        $this->actingAs($admin)
            ->get(route('workspaces.show', $workspace))
            ->assertInertia(fn (Assert $page) => $page
                ->where('canDeleteWorkspace', false)
                ->where('canManageMembers', false)
                ->where('canManageProjects', true));

        $this->actingAs($member)
            ->get(route('workspaces.show', $workspace))
            ->assertInertia(fn (Assert $page) => $page
                ->where('canDeleteWorkspace', false)
                ->where('canManageMembers', false)
                ->where('canManageProjects', false));
    }

    public function test_project_update_rejects_a_key_already_used_in_the_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create(['key' => 'ONE']);
        $existingProject = Project::factory()->for($workspace)->create(['key' => 'TWO']);

        $this->actingAs($workspace->owner)
            ->from(route('projects.board', $project))
            ->patch(route('projects.update', $project), [
                'name' => 'Updated project',
                'key' => $existingProject->key,
            ])
            ->assertSessionHasErrors('key');

        $this->assertDatabaseHas('projects', [
            'id' => $project->getKey(),
            'name' => $project->name,
            'key' => 'ONE',
        ]);
    }
}
