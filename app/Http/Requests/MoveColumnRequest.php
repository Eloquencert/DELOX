<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Column;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MoveColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        $column = $this->route('column');

        return $column instanceof Column
            && ($this->user()?->can('move', $column) ?? false);
    }

    public function rules(): array
    {
        /** @var Column $column */
        $column = $this->route('column');

        return [
            'prev_id' => [
                'nullable',
                'integer',
                'different:next_id',
                'not_in:'.$column->getKey(),
                'exists:columns,id,project_id,'.$column->project_id,
            ],
            'next_id' => [
                'nullable',
                'integer',
                'not_in:'.$column->getKey(),
                'exists:columns,id,project_id,'.$column->project_id,
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('prev_id') === $this->input('next_id')
                    && $this->input('prev_id') !== null) {
                    $validator->errors()->add('next_id', 'The neighboring columns must be different.');
                }

                if ($validator->errors()->hasAny(['prev_id', 'next_id'])) {
                    return;
                }

                $previous = $this->input('prev_id') === null
                    ? null
                    : Column::query()->find($this->input('prev_id'));
                $next = $this->input('next_id') === null
                    ? null
                    : Column::query()->find($this->input('next_id'));

                if ($previous !== null && $next !== null
                    && strcmp($previous->position, $next->position) >= 0) {
                    $validator->errors()->add('next_id', 'The neighboring columns must be in position order.');
                }
            },
        ];
    }
}
