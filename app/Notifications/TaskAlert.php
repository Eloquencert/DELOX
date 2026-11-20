<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Notification;

class TaskAlert extends Notification
{
    public function __construct(
        public readonly string $event,
        public readonly string $message,
        public readonly int $projectId,
        public readonly string $projectKey,
        public readonly int $taskId,
        public readonly int $taskNumber,
        public readonly string $taskTitle,
    ) {}

    public static function assigned(Task $task): self
    {
        return self::forTask(
            'assignment',
            "You were assigned to {$task->project->key}-{$task->number}: {$task->title}.",
            $task,
        );
    }

    public static function commented(Task $task, string $commenterName): self
    {
        return self::forTask(
            'comment',
            "{$commenterName} commented on {$task->project->key}-{$task->number}: {$task->title}.",
            $task,
        );
    }

    public static function dueTomorrow(Task $task): self
    {
        return self::forTask(
            'due_soon',
            "{$task->project->key}-{$task->number}: {$task->title} is due tomorrow.",
            $task,
        );
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, int|string>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'message' => $this->message,
            'project_id' => $this->projectId,
            'project_key' => $this->projectKey,
            'task_id' => $this->taskId,
            'task_number' => $this->taskNumber,
            'task_title' => $this->taskTitle,
        ];
    }

    private static function forTask(string $event, string $message, Task $task): self
    {
        $task->loadMissing('project:id,key');

        return new self(
            $event,
            $message,
            (int) $task->project_id,
            $task->project->key,
            (int) $task->getKey(),
            (int) $task->number,
            $task->title,
        );
    }
}
