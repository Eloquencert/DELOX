<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Column;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ColumnCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Column $column) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('project.'.$this->column->project_id)];
    }

    public function broadcastAs(): string
    {
        return 'ColumnCreated';
    }

    public function broadcastWith(): array
    {
        return ['column' => $this->column->toArray()];
    }
}
