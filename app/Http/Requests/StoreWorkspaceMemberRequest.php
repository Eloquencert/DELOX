<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWorkspaceMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && ($this->user()?->can('manageMembers', $workspace) ?? false);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
            'role' => ['required', Rule::in(['admin', 'member'])],
        ];
    }

    public function after(): array
    {
        /** @var Workspace $workspace */
        $workspace = $this->route('workspace');

        return [
            function (Validator $validator) use ($workspace): void {
                if ($validator->errors()->has('email')) {
                    return;
                }

                if ($workspace->users()->where('email', $this->input('email'))->exists()) {
                    $validator->errors()->add('email', 'This person is already a workspace member.');
                }
            },
        ];
    }
}
