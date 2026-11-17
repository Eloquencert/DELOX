<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\ColumnCreated;
use App\Events\ColumnDeleted;
use App\Events\ColumnMoved;
use App\Http\Requests\MoveColumnRequest;
use App\Http\Requests\StoreColumnRequest;
use App\Models\Column;
use App\Models\Project;
use App\Services\FractionalIndexingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ColumnController extends Controller
{
    public function store(
        StoreColumnRequest $request,
        Project $project,
        FractionalIndexingService $indexing,
    ): JsonResponse {
        $previousPosition = $project->columns()->reorder('position', 'desc')->value('position');
        $column = $project->columns()->create([
            'name' => $request->validated('name'),
            'position' => $indexing->generateKeyBetween($previousPosition, null),
        ]);

        broadcast(new ColumnCreated($column))->toOthers();

        return response()->json(['column' => $column], 201);
    }

    public function move(
        MoveColumnRequest $request,
        Column $column,
        FractionalIndexingService $indexing,
    ): JsonResponse {
        $data = $request->validated();

        $column = DB::transaction(function () use ($column, $data, $indexing): Column {
            $lockedColumn = Column::query()->lockForUpdate()->findOrFail($column->getKey());
            $previous = isset($data['prev_id'])
                ? Column::query()->lockForUpdate()->findOrFail($data['prev_id'])
                : null;
            $next = isset($data['next_id'])
                ? Column::query()->lockForUpdate()->findOrFail($data['next_id'])
                : null;

            if ($previous !== null && $next !== null && strcmp($previous->position, $next->position) >= 0) {
                abort(422, 'The neighboring column positions are out of order.');
            }

            $lockedColumn->position = $indexing->generateKeyBetween(
                $previous?->position,
                $next?->position,
            );
            $lockedColumn->save();

            return $lockedColumn;
        });

        broadcast(new ColumnMoved($column))->toOthers();

        return response()->json(['column' => $column]);
    }

    public function destroy(Column $column): JsonResponse
    {
        $this->authorize('delete', $column);

        $projectId = (int) $column->project_id;
        $columnId = (int) $column->getKey();
        $column->delete();

        broadcast(new ColumnDeleted($projectId, $columnId))->toOthers();

        return response()->json(['deleted' => true]);
    }
}
