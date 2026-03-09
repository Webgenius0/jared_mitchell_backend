<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')->id ?? $this->route('role');

        return [
            'name'           => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($roleId)],
            'permissions'    => ['nullable', 'array'],
            'permissions.*'  => ['exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A role with this name already exists.',
        ];
    }
}
