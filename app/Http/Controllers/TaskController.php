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
use App\Notifications\TaskAlert;
use App\Services\ActivityLogger;
use App\Services\FractionalIndexingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function store(
        StoreTaskRequest $request,
        Project $project,
        FractionalIndexingService $indexing,
        ActivityLogger $activityLogger,
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
        $activityLogger->record(
            $user,
            $project,
            'task.created',
            "Created {$project->key}-{$task->number}: {$task->title}.",
            $task,
        );

        if ($task->assignee !== null && $task->assignee_id !== $user->getKey()) {
            $task->assignee->notify(TaskAlert::assigned($task));
        }

        $task->load(['creator:id,name', 'assignee:id,name', 'comments.user:id,name']);

        broadcast(new TaskCreated($task))->toOthers();

        return response()->json(['task' => $task], 201);
    }

    public function update(
        UpdateTaskRequest $request,
        Task $task,
        ActivityLogger $activityLogger,
    ): JsonResponse {
        $data = $request->validated();
        /** @var User $user */
        $user = $request->user();
        $originalValues = [];

        foreach ($data as $field => $value) {
            $originalValues[$field] = $task->getRawOriginal($field);
        }

        $changedFields = [];

        foreach ($data as $field => $value) {
            $originalValue = $originalValues[$field];
            $normalizedOriginal = $originalValue === null ? null : (string) $originalValue;
            $normalizedValue = $value === null ? null : (string) $value;

            if ($normalizedOriginal !== $normalizedValue) {
                $changedFields[] = $field;
            }
        }

        $previousAssigneeId = $task->assignee_id;
        $assignmentChanged = in_array('assignee_id', $changedFields, true);
        $reminderScheduleChanged = $assignmentChanged
            || in_array('due_date', $changedFields, true);

        $task->fill($data);

        if ($reminderScheduleChanged) {
            $task->due_date_reminder_sent_at = null;
        }

        $task->save();

        if ($changedFields !== []) {
            $fieldLabels = [
                'title' => 'title',
                'description' => 'description',
                'priority' => 'priority',
                'assignee_id' => 'assignee',
                'due_date' => 'due date',
            ];
            $labels = array_map(
                fn (string $field): string => $fieldLabels[$field] ?? $field,
                $changedFields,
            );
            $task->loadMissing('project:id,key');
            $taskCode = "{$task->project->key}-{$task->number}";

            $activityLogger->record(
                $user,
                $task->project,
                'task.updated',
                "Updated {$taskCode}: ".implode(', ', $labels).'.',
                $task,
                ['fields' => $changedFields],
            );
        }

        if ($assignmentChanged && $task->assignee_id !== null && $task->assignee_id !== $previousAssigneeId) {
            $assignee = $task->assignee;

            if ($assignee !== null && $assignee->isNot($user)) {
                $assignee->notify(TaskAlert::assigned($task));
            }
        }

        $task->load(['creator:id,name', 'assignee:id,name', 'comments.user:id,name']);

        broadcast(new TaskUpdated($task))->toOthers();

        return response()->json(['task' => $task]);
    }

    public function move(
        MoveTaskRequest $request,
        Task $task,
        FractionalIndexingService $indexing,
        ActivityLogger $activityLogger,
    ): JsonResponse {
        $data = $request->validated();
        $previousColumnId = (int) $task->column_id;
        $previousColumnName = $task->column()->value('name');

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

        if ((int) $task->column_id !== $previousColumnId) {
            $destinationColumn = Column::query()->findOrFail($task->column_id);
            $task->loadMissing('project:id,key');
            $taskCode = "{$task->project->key}-{$task->number}";

            /** @var User $user */
            $user = $request->user();
            $activityLogger->record(
                $user,
                $task->project,
                'task.moved',
                "Moved {$taskCode} from {$previousColumnName} to {$destinationColumn->name}.",
                $task,
                ['from' => $previousColumnName, 'to' => $destinationColumn->name],
            );
        }

        broadcast(new TaskMoved($task))->toOthers();

        return response()->json(['task' => $task]);
    }

    public function destroy(
        Request $request,
        Task $task,
        ActivityLogger $activityLogger,
    ): JsonResponse {
        $this->authorize('delete', $task);

        $projectId = (int) $task->project_id;
        $taskId = (int) $task->getKey();
        $task->loadMissing('project:id,key');
        $taskCode = "{$task->project->key}-{$task->number}";

        /** @var User $user */
        $user = $request->user();
        $activityLogger->record(
            $user,
            $task->project,
            'task.deleted',
            "Deleted {$taskCode}: {$task->title}.",
            $task,
        );

        $task->delete();

        broadcast(new TaskDeleted($projectId, $taskId))->toOthers();

        return response()->json(['deleted' => true]);
    }
}
