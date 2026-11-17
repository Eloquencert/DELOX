<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MoveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task instanceof Task
            && ($this->user()?->can('move', $task) ?? false);
    }

    public function rules(): array
    {
        /** @var Task $task */
        $task = $this->route('task');

        return [
            'column_id' => [
                'required',
                'integer',
                Rule::exists('columns', 'id')->where('project_id', $task->project_id),
            ],
            'prev_id' => [
                'nullable',
                'integer',
                'different:next_id',
                'not_in:'.$task->getKey(),
                Rule::exists('tasks', 'id')->where('project_id', $task->project_id),
            ],
            'next_id' => [
                'nullable',
                'integer',
                'not_in:'.$task->getKey(),
                Rule::exists('tasks', 'id')->where('project_id', $task->project_id),
            ],
        ];
    }

    public function after(): array
    {
        /** @var Task $task */
        $task = $this->route('task');

        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['column_id', 'prev_id', 'next_id'])) {
                    return;
                }

                $columnId = $this->input('column_id');
                $previous = null;
                $next = null;

                foreach (['prev_id', 'next_id'] as $neighborKey) {
                    $neighborId = $this->input($neighborKey);

                    if ($neighborId === null) {
                        continue;
                    }

                    $neighbor = Task::query()->find($neighborId);

                    if ($neighbor !== null && (int) $neighbor->column_id !== (int) $columnId) {
                        $validator->errors()->add(
                            $neighborKey,
                            'The neighboring task must belong to the destination column.',
                        );
                    }

                    if ($neighborKey === 'prev_id') {
                        $previous = $neighbor;
                    } else {
                        $next = $neighbor;
                    }
                }

                if ($this->input('prev_id') === $this->input('next_id')
                    && $this->input('prev_id') !== null) {
                    $validator->errors()->add('next_id', 'The neighboring tasks must be different.');
                }

                if ($previous !== null && $next !== null
                    && strcmp($previous->position, $next->position) >= 0) {
                    $validator->errors()->add('next_id', 'The neighboring tasks must be in position order.');
                }
            },
        ];
    }
}
