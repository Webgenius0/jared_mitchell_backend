<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:100', 'unique:roles,name'],
            'guard_name'     => ['required', 'string', 'max:100'],
            'permissions'    => ['nullable', 'array'],
            'permissions.*'  => ['exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A role with this name already exists.',
            'guard_name.required' => 'The guard name is required.',
        ];
    }
}
