<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\TaskCommentCreated;
use App\Http\Requests\StoreTaskCommentRequest;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAlert;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;

class TaskCommentController extends Controller
{
    public function store(
        StoreTaskCommentRequest $request,
        Task $task,
        ActivityLogger $activityLogger,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $task->loadMissing([
            'creator:id,name',
            'assignee:id,name',
            'project:id,key,workspace_id',
            'project.workspace',
        ]);

        $comment = $task->comments()->create([
            'user_id' => $user->getKey(),
            'body' => $request->validated('body'),
        ]);
        $comment->load('user:id,name');

        $taskCode = "{$task->project->key}-{$task->number}";
        $activityLogger->record(
            $user,
            $task->project,
            'task.commented',
            "Added a comment to {$taskCode}.",
            $task,
        );

        $recipients = collect([$task->creator, $task->assignee])
            ->filter(fn (?User $recipient): bool => $recipient !== null && $recipient->isNot($user))
            ->unique('id');
        $workspaceMemberIds = $task->project->workspace
            ->users()
            ->whereKey($recipients->pluck('id')->all())
            ->pluck('users.id');

        $recipients
            ->whereIn('id', $workspaceMemberIds)
            ->each(fn (User $recipient) => $recipient->notify(
                TaskAlert::commented($task, $user->name),
            ));

        broadcast(new TaskCommentCreated($comment))->toOthers();

        return response()->json(['comment' => $comment], 201);
    }
}
