<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkspaceMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && ($this->user()?->can('manageMembers', $workspace) ?? false);
    }

    public function rules(): array
    {
        /** @var Workspace $workspace */
        $workspace = $this->route('workspace');
        /** @var User $member */
        $member = $this->route('user');

        if ((int) $member->getKey() === (int) $workspace->owner_id) {
            return ['role' => ['prohibited']];
        }

        return [
            'role' => ['required', Rule::in(['admin', 'member'])],
        ];
    }
}
