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
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_mark_their_notification_as_read(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create();
        $workspace->owner->notify(TaskAlert::assigned($task));
        $notification = $workspace->owner->notifications()->firstOrFail();

        $this->actingAs($workspace->owner)
            ->patch(route('notifications.read', $notification->getKey()))
            ->assertRedirect();

        $this->assertNotNull(
            $workspace->owner->notifications()->firstOrFail()->read_at,
        );
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create();
        $recipient = User::factory()->create();
        $recipient->notify(TaskAlert::assigned($task));
        $notification = $recipient->notifications()->firstOrFail();

        $this->actingAs($workspace->owner)
            ->patch(route('notifications.read', $notification->getKey()))
            ->assertNotFound();

        $this->assertNull($recipient->notifications()->firstOrFail()->read_at);
    }

    public function test_all_of_a_users_unread_notifications_can_be_marked_read(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create();
        $workspace->owner->notify(TaskAlert::assigned($task));
        $workspace->owner->notify(TaskAlert::commented($task, 'Teammate'));

        $this->actingAs($workspace->owner)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(
            0,
            $workspace->owner->unreadNotifications()->count(),
        );
    }

    public function test_board_exposes_recent_notifications_and_the_unread_count(): void
    {
        $workspace = Workspace::factory()->create();
        $project = Project::factory()->for($workspace)->create();
        $column = Column::factory()->for($project)->create();
        $task = Task::factory()->forProject($project, $column)->create();
        $workspace->owner->notify(TaskAlert::assigned($task));

        $this->actingAs($workspace->owner)
            ->get(route('projects.board', $project))
            ->assertInertia(fn (Assert $page) => $page
                ->where('unreadNotificationsCount', 1)
                ->has('notifications', 1)
                ->where('notifications.0.data.event', 'assignment')
                ->where('notifications.0.data.task_id', $task->getKey()));
    }
}
