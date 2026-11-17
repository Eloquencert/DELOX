<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\TaskComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCommentCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TaskComment $comment) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('project.'.$this->comment->task->project_id)];
    }

    public function broadcastAs(): string
    {
        return 'TaskCommentCreated';
    }

    public function broadcastWith(): array
    {
        return ['comment' => $this->comment->toArray()];
    }
}
