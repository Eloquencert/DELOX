<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Workspace;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateDefaultWorkspace
{
    public function handle(Registered $event): void
    {
        DB::transaction(function () use ($event): void {
            $name = "{$event->user->name}'s Workspace";
            $slug = Str::slug($event->user->name) ?: 'workspace';

            Workspace::query()->firstOrCreate(
                ['slug' => "{$slug}-workspace-{$event->user->getKey()}"],
                ['name' => $name, 'owner_id' => $event->user->getKey()],
            );
        });
    }
}
