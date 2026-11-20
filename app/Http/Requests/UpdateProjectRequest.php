<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && ($this->user()?->can('update', $project) ?? false);
    }

    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');

        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => [
                'required',
                'string',
                'max:6',
                'regex:/^[A-Z]{2,6}$/',
                Rule::unique('projects', 'key')
                    ->where('workspace_id', $project->workspace_id)
                    ->ignore($project->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
