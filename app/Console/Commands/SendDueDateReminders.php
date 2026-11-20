<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskAlert;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('notifications:send-due-date-reminders')]
#[Description('Send in-app notifications for tasks due tomorrow.')]
class SendDueDateReminders extends Command
{
    public function handle(): int
    {
        $sentCount = 0;
        $reminderDate = today()->addDay()->toDateString();

        Task::query()
            ->whereDate('due_date', $reminderDate)
            ->whereNotNull('assignee_id')
            ->whereNull('due_date_reminder_sent_at')
            ->select('id')
            ->chunkById(100, function ($tasks) use (&$sentCount, $reminderDate): void {
                foreach ($tasks as $taskStub) {
                    $sent = DB::transaction(function () use ($taskStub, $reminderDate): bool {
                        $task = Task::query()
                            ->lockForUpdate()
                            ->find($taskStub->getKey());

                        if (
                            $task === null
                            || $task->assignee_id === null
                            || $task->due_date === null
                            || $task->due_date->toDateString() !== $reminderDate
                            || $task->due_date_reminder_sent_at !== null
                        ) {
                            return false;
                        }

                        $task->loadMissing(['assignee', 'project.workspace']);
                        $assignee = $task->assignee;

                        if ($assignee === null) {
                            return false;
                        }

                        if (! $task->project->workspace->users()
                            ->whereKey($assignee->getKey())
                            ->exists()) {
                            return false;
                        }

                        $assignee->notify(TaskAlert::dueTomorrow($task));
                        $task->forceFill(['due_date_reminder_sent_at' => now()])->save();

                        return true;
                    });

                    if ($sent) {
                        $sentCount++;
                    }
                }
            });

        $this->info("Sent {$sentCount} due-date reminder(s).");

        return self::SUCCESS;
    }
}
