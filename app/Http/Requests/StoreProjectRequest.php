<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && ($this->user()?->can('create', [Project::class, $workspace]) ?? false);
    }

    public function rules(): array
    {
        /** @var Workspace $workspace */
        $workspace = $this->route('workspace');

        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => [
                'required',
                'string',
                'max:6',
                'regex:/^[A-Z]{2,6}$/',
                Rule::unique('projects', 'key')->where('workspace_id', $workspace->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
