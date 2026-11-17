<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\TaskCommentCreated;
use App\Http\Requests\StoreTaskCommentRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TaskCommentController extends Controller
{
    public function store(StoreTaskCommentRequest $request, Task $task): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $comment = $task->comments()->create([
            'user_id' => $user->getKey(),
            'body' => $request->validated('body'),
        ]);
        $comment->load('user:id,name');

        broadcast(new TaskCommentCreated($comment))->toOthers();

        return response()->json(['comment' => $comment], 201);
    }
}
