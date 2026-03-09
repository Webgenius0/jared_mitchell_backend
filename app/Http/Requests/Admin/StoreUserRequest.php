<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles'    => ['nullable', 'array'],
            'roles.*'  => ['exists:roles,name'],
            'status'   => ['nullable', 'in:active,inactive'],
            'avatar'   => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'    => 'This email address is already registered.',
            'phone.unique'    => 'This phone number is already registered.',
            'password.min'    => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'avatar.max'      => 'Avatar image must not exceed 2MB.',
        ];
    }
}
