<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\TaskAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TaskAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_creation_notifies_a_different_assignee_and_logs_activity(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $assignee = User::factory()->create();
        $workspace->users()->attach($assignee, ['role' => 'member']);

        $response = $this->actingAs($workspace->owner)->postJson(
            route('tasks.store', $project),
            [
                'column_id' => $column->getKey(),
                'title' => 'Prepare release',
                'priority' => 'high',
                'assignee_id' => $assignee->getKey(),
            ],
        );

        $response->assertCreated();
        $notification = $assignee->notifications()->firstOrFail();

        $this->assertSame(TaskAlert::class, $notification->type);
        $this->assertSame('assignment', $notification->data['event']);
        $this->assertSame('Prepare release', $notification->data['task_title']);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $workspace->owner_id,
            'type' => TaskAlert::class,
        ]);
        $this->assertDatabaseHas('activities', [
            'project_id' => $project->getKey(),
            'action' => 'task.created',
        ]);
    }

    public function test_comment_notifies_task_participants_except_the_commenter(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $assignee = User::factory()->create();
        $admin = User::factory()->create();
        $workspace->users()->attach($assignee, ['role' => 'member']);
        $workspace->users()->attach($admin, ['role' => 'admin']);
        $task = Task::factory()->forProject($project, $column)->create([
            'creator_id' => $workspace->owner_id,
            'assignee_id' => $assignee->getKey(),
            'title' => 'Review design',
        ]);

        $this->actingAs($admin)
            ->postJson(route('tasks.comments.store', $task), ['body' => 'Ready for review.'])
            ->assertCreated();

        $this->assertDatabaseCount('notifications', 2);
        $this->assertSame('comment', $workspace->owner->notifications()->firstOrFail()->data['event']);
        $this->assertSame('comment', $assignee->notifications()->firstOrFail()->data['event']);
        $this->assertSame(0, $admin->notifications()->count());
        $this->assertDatabaseHas('activities', [
            'project_id' => $project->getKey(),
            'task_id' => $task->getKey(),
            'user_id' => $admin->getKey(),
            'action' => 'task.commented',
        ]);
    }

    public function test_comment_does_not_notify_an_assignee_removed_from_the_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $assignee = User::factory()->create();
        $admin = User::factory()->create();
        $workspace->users()->attach($assignee, ['role' => 'member']);
        $workspace->users()->attach($admin, ['role' => 'admin']);
        $task = Task::factory()->forProject($project, $column)->create([
            'creator_id' => $workspace->owner_id,
            'assignee_id' => $assignee->getKey(),
        ]);
        $workspace->users()->detach($assignee);

        $this->actingAs($admin)
            ->postJson(route('tasks.comments.store', $task), ['body' => 'Follow-up.'])
            ->assertCreated();

        $this->assertSame(0, $assignee->notifications()->count());
        $this->assertSame(1, $workspace->owner->notifications()->count());
    }

    public function test_due_date_command_sends_only_one_tomorrow_reminder_per_task(): void
    {
        $this->travelTo('2026-11-20 07:55:00');
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $assignee = User::factory()->create();
        $workspace->users()->attach($assignee, ['role' => 'member']);

        $dueTomorrow = Task::factory()->forProject($project, $column)->create([
            'assignee_id' => $assignee->getKey(),
            'due_date' => '2026-11-21',
        ]);
        Task::factory()->forProject($project, $column)->create([
            'assignee_id' => null,
            'due_date' => '2026-11-21',
        ]);
        Task::factory()->forProject($project, $column)->create([
            'assignee_id' => $assignee->getKey(),
            'due_date' => '2026-11-22',
        ]);

        Artisan::call('notifications:send-due-date-reminders');
        Artisan::call('notifications:send-due-date-reminders');

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame('due_soon', $assignee->notifications()->firstOrFail()->data['event']);
        $this->assertNotNull($dueTomorrow->fresh()->due_date_reminder_sent_at);
    }

    public function test_due_date_command_skips_an_assignee_removed_from_the_workspace(): void
    {
        $this->travelTo('2026-11-20 07:55:00');
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $assignee = User::factory()->create();
        $workspace->users()->attach($assignee, ['role' => 'member']);
        $task = Task::factory()->forProject($project, $column)->create([
            'assignee_id' => $assignee->getKey(),
            'due_date' => '2026-11-21',
        ]);
        $workspace->users()->detach($assignee);

        Artisan::call('notifications:send-due-date-reminders');

        $this->assertSame(0, $assignee->notifications()->count());
        $this->assertNull($task->fresh()->due_date_reminder_sent_at);
    }

    public function test_changing_a_due_date_resets_its_reminder_marker(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create([
            'due_date' => '2026-11-21',
        ]);
        $task->forceFill(['due_date_reminder_sent_at' => now()])->save();

        $this->actingAs($workspace->owner)
            ->patchJson(route('tasks.update', $task), ['due_date' => '2026-11-22'])
            ->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'due_date_reminder_sent_at' => null,
        ]);
    }

    public function test_changing_an_assignee_resets_its_reminder_marker(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $previousAssignee = User::factory()->create();
        $newAssignee = User::factory()->create();
        $workspace->users()->attach($previousAssignee, ['role' => 'member']);
        $workspace->users()->attach($newAssignee, ['role' => 'member']);
        $task = Task::factory()->forProject($project, $column)->create([
            'assignee_id' => $previousAssignee->getKey(),
            'due_date' => '2026-11-21',
        ]);
        $task->forceFill(['due_date_reminder_sent_at' => now()])->save();

        $this->actingAs($workspace->owner)
            ->patchJson(route('tasks.update', $task), [
                'assignee_id' => $newAssignee->getKey(),
            ])
            ->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'due_date_reminder_sent_at' => null,
        ]);
        $this->assertSame(
            'assignment',
            $newAssignee->notifications()->firstOrFail()->data['event'],
        );
    }
}
