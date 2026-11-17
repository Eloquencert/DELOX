<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskMoved;
use App\Events\TaskUpdated;
use App\Http\Requests\MoveTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\FractionalIndexingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function store(
        StoreTaskRequest $request,
        Project $project,
        FractionalIndexingService $indexing,
    ): JsonResponse {
        $data = $request->validated();
        $column = Column::query()->findOrFail($data['column_id']);
        $previousPosition = $column->tasks()->reorder('position', 'desc')->value('position');

        /** @var User $user */
        $user = $request->user();
        $task = $project->tasks()->create([
            ...$data,
            'creator_id' => $user->getKey(),
            'position' => $indexing->generateKeyBetween($previousPosition, null),
        ]);
        $task->load(['creator:id,name', 'assignee:id,name', 'comments.user:id,name']);

        broadcast(new TaskCreated($task))->toOthers();

        return response()->json(['task' => $task], 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());
        $task->load(['creator:id,name', 'assignee:id,name', 'comments.user:id,name']);

        broadcast(new TaskUpdated($task))->toOthers();

        return response()->json(['task' => $task]);
    }

    public function move(
        MoveTaskRequest $request,
        Task $task,
        FractionalIndexingService $indexing,
    ): JsonResponse {
        $data = $request->validated();

        $task = DB::transaction(function () use ($data, $task, $indexing): Task {
            $lockedTask = Task::query()->lockForUpdate()->findOrFail($task->getKey());
            $previous = isset($data['prev_id'])
                ? Task::query()->lockForUpdate()->findOrFail($data['prev_id'])
                : null;
            $next = isset($data['next_id'])
                ? Task::query()->lockForUpdate()->findOrFail($data['next_id'])
                : null;

            if ($previous !== null && $next !== null && strcmp($previous->position, $next->position) >= 0) {
                abort(422, 'The neighboring task positions are out of order.');
            }

            $lockedTask->column_id = $data['column_id'];
            $lockedTask->position = $indexing->generateKeyBetween(
                $previous?->position,
                $next?->position,
            );
            $lockedTask->save();

            return $lockedTask->load(['creator:id,name', 'assignee:id,name', 'comments.user:id,name']);
        });

        broadcast(new TaskMoved($task))->toOthers();

        return response()->json(['task' => $task]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $projectId = (int) $task->project_id;
        $taskId = (int) $task->getKey();
        $task->delete();

        broadcast(new TaskDeleted($projectId, $taskId))->toOthers();

        return response()->json(['deleted' => true]);
    }
}
