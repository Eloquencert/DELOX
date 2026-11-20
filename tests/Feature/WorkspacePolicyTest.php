<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspacePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_roles_have_the_expected_management_permissions(): void
    {
        $workspace = Workspace::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $workspace->users()->attach($admin, ['role' => 'admin']);
        $workspace->users()->attach($member, ['role' => 'member']);

        $this->assertTrue($workspace->owner->can('view', $workspace));
        $this->assertTrue($workspace->owner->can('update', $workspace));
        $this->assertTrue($workspace->owner->can('manageMembers', $workspace));
        $this->assertTrue($workspace->owner->can('delete', $workspace));
        $this->assertFalse($admin->can('update', $workspace));
        $this->assertFalse($admin->can('manageMembers', $workspace));
        $this->assertFalse($admin->can('delete', $workspace));
        $this->assertTrue($member->can('view', $workspace));
        $this->assertFalse($member->can('manageMembers', $workspace));
        $this->assertFalse($member->can('update', $workspace));
        $this->assertFalse($outsider->can('view', $workspace));
    }

    public function test_project_board_records_are_accessible_only_to_workspace_members(): void
    {
        $workspace = Workspace::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $workspace->users()->attach($admin, ['role' => 'admin']);
        $workspace->users()->attach($member, ['role' => 'member']);
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create();

        $this->assertTrue($member->can('view', $project));
        $this->assertTrue($workspace->owner->can('create', [Project::class, $workspace]));
        $this->assertTrue($workspace->owner->can('delete', $project));
        $this->assertFalse($member->can('update', $project));
        $this->assertFalse($member->can('delete', $project));
        $this->assertFalse($member->can('create', [Project::class, $workspace]));
        $this->assertTrue($workspace->owner->can('update', $project));
        $this->assertTrue($admin->can('create', [Project::class, $workspace]));
        $this->assertTrue($admin->can('update', $project));
        $this->assertTrue($admin->can('delete', $project));
        $this->assertTrue($member->can('view', $column));
        $this->assertFalse($member->can('create', [Column::class, $project]));
        $this->assertFalse($member->can('move', $column));
        $this->assertTrue($member->can('view', $task));
        $this->assertFalse($member->can('create', [Task::class, $project]));
        $this->assertFalse($member->can('update', $task));
        $this->assertFalse($member->can('move', $task));
        $this->assertFalse($member->can('delete', $task));
        $this->assertFalse($member->can('comment', $task));
        $this->assertTrue($admin->can('create', [Column::class, $project]));
        $this->assertTrue($admin->can('create', [Task::class, $project]));
        $this->assertTrue($admin->can('move', $task));
        $this->assertFalse($outsider->can('view', $project));
        $this->assertFalse($outsider->can('view', $column));
        $this->assertFalse($outsider->can('view', $task));
    }
}
