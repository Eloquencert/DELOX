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

class BoardMutationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_create_and_move_a_task_between_columns(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $backlog = Column::factory()->for($project)->create([
            'name' => 'Backlog',
            'position' => 'a0',
        ]);
        $inProgress = Column::factory()->for($project)->create([
            'name' => 'In Progress',
            'position' => 'a1',
        ]);

        $createResponse = $this->actingAs($workspace->owner)->postJson(
            route('tasks.store', $project),
            [
                'column_id' => $backlog->getKey(),
                'title' => 'Ship the board',
                'priority' => 'high',
            ],
        );
        $createResponse->assertCreated()->assertJsonPath('task.number', 1);
        $taskId = $createResponse->json('task.id');
        $task = Task::query()->findOrFail($taskId);

        $this->patchJson(route('tasks.move', $task), [
            'column_id' => $inProgress->getKey(),
            'prev_id' => null,
            'next_id' => null,
        ])->assertOk()->assertJsonPath('task.column_id', $inProgress->getKey());

        $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'column_id' => $inProgress->getKey(),
            'number' => 1,
        ]);
    }

    public function test_task_update_and_comment_are_persisted(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create();

        $this->actingAs($workspace->owner)
            ->patchJson(route('tasks.update', $task), [
                'title' => 'Updated title',
                'priority' => 'urgent',
                'description' => 'More details',
                'due_date' => '2027-01-15',
            ])
            ->assertOk()
            ->assertJsonPath('task.title', 'Updated title')
            ->assertJsonPath('task.priority', 'urgent');

        $this->postJson(route('tasks.comments.store', $task), [
            'body' => 'Looks good to me.',
        ])->assertCreated()->assertJsonPath('comment.body', 'Looks good to me.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'description' => 'More details',
            'due_date' => '2027-01-15',
        ]);
        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->getKey(),
            'user_id' => $workspace->owner_id,
            'body' => 'Looks good to me.',
        ]);
    }

    public function test_task_move_rejects_a_neighbor_from_another_column(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $firstColumn = Column::factory()->for($project)->create();
        $secondColumn = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $firstColumn)->create();
        $neighbor = Task::factory()->forProject($project, $firstColumn)->create();

        $this->actingAs($workspace->owner)
            ->patchJson(route('tasks.move', $task), [
                'column_id' => $secondColumn->getKey(),
                'prev_id' => $neighbor->getKey(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('prev_id');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'column_id' => $firstColumn->getKey(),
        ]);
    }

    public function test_member_can_reorder_columns_and_delete_a_task(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $first = Column::factory()->for($project)->create(['position' => 'a0']);
        $second = Column::factory()->for($project)->create(['position' => 'a1']);
        $third = Column::factory()->for($project)->create(['position' => 'a2']);
        $task = Task::factory()->forProject($project, $first)->create();

        $this->actingAs($workspace->owner)
            ->patchJson(route('columns.move', $first), [
                'prev_id' => $third->getKey(),
                'next_id' => null,
            ])
            ->assertOk();

        $this->deleteJson(route('tasks.destroy', $task))
            ->assertOk()
            ->assertJsonPath('deleted', true);

        $this->assertDatabaseMissing('tasks', ['id' => $task->getKey()]);
        $this->assertDatabaseHas('columns', ['id' => $second->getKey()]);
    }

    public function test_user_from_another_workspace_cannot_update_a_task(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->patchJson(route('tasks.update', $task), ['title' => 'Unauthorized'])
            ->assertForbidden();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->getKey(),
            'title' => 'Unauthorized',
        ]);
    }
}
