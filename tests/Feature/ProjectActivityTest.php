<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_and_column_changes_are_shown_in_the_activity_feed(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $firstColumn = Column::factory()->for($project)->create(['position' => 'a0']);
        $secondColumn = Column::factory()->for($project)->create(['position' => 'a1']);

        $this->actingAs($workspace->owner)
            ->postJson(route('columns.store', $project), ['name' => 'Testing'])
            ->assertCreated();
        $createdColumn = $project->columns()->where('name', 'Testing')->firstOrFail();

        $this->patchJson(route('columns.move', $firstColumn), [
            'prev_id' => $secondColumn->getKey(),
            'next_id' => null,
        ])->assertOk();

        $this->deleteJson(route('columns.destroy', $secondColumn))->assertOk();

        $this->patch(route('projects.update', $project), [
            'name' => 'Operations Hub',
            'key' => $project->key,
            'description' => $project->description,
        ])->assertRedirect(route('projects.board', $project));

        $this->assertDatabaseHas('activities', [
            'project_id' => $project->getKey(),
            'user_id' => $workspace->owner_id,
            'action' => 'column.created',
            'description' => 'Created column "Testing".',
        ]);
        $this->assertDatabaseHas('activities', [
            'project_id' => $project->getKey(),
            'action' => 'column.reordered',
        ]);
        $this->assertDatabaseHas('activities', [
            'project_id' => $project->getKey(),
            'action' => 'column.deleted',
        ]);
        $this->assertDatabaseHas('activities', [
            'project_id' => $project->getKey(),
            'action' => 'project.updated',
        ]);

        $this->get(route('projects.board', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('activities', 4)
                ->where('activities.0.action', 'project.updated')
                ->where('activities.0.user.name', $workspace->owner->name)
                ->where('activities.3.action', 'column.created'));

        $this->assertDatabaseHas('columns', ['id' => $createdColumn->getKey()]);
    }

    public function test_task_updates_moves_and_deletions_are_preserved_in_history(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $backlog = Column::factory()->for($project)->create(['position' => 'a0']);
        $inProgress = Column::factory()->for($project)->create(['position' => 'a1']);
        $task = Task::factory()->forProject($project, $backlog)->create([
            'title' => 'Initial title',
        ]);

        $this->actingAs($workspace->owner)
            ->patchJson(route('tasks.update', $task), ['title' => 'Updated title'])
            ->assertOk();

        $this->patchJson(route('tasks.move', $task), [
            'column_id' => $inProgress->getKey(),
            'prev_id' => null,
            'next_id' => null,
        ])->assertOk();

        $this->deleteJson(route('tasks.destroy', $task))->assertOk();

        $this->assertDatabaseHas('activities', [
            'project_id' => $project->getKey(),
            'action' => 'task.updated',
            'description' => "Updated {$project->key}-1: title.",
        ]);
        $this->assertDatabaseHas('activities', [
            'project_id' => $project->getKey(),
            'action' => 'task.moved',
            'description' => "Moved {$project->key}-1 from {$backlog->name} to {$inProgress->name}.",
        ]);
        $this->assertDatabaseHas('activities', [
            'project_id' => $project->getKey(),
            'task_id' => null,
            'action' => 'task.deleted',
            'description' => "Deleted {$project->key}-1: Updated title.",
        ]);

        $this->get(route('projects.board', $project))
            ->assertInertia(fn (Assert $page) => $page
                ->has('activities', 3)
                ->where('activities.0.action', 'task.deleted')
                ->where('activities.0.task_id', null));
    }
}
