<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id'              => 'required|exists:users,id',
            'business_category_id' => 'required|exists:business_categories,id',
            'owner_name'           => 'required|string|max:255',
            'business_name'        => 'required|string|max:255',
            'slug'                 => 'nullable|string|max:255|unique:businesses,slug',
            'year_founded'         => 'required|integer|min:1800|max:' . date('Y'),
            'website'              => 'nullable|string|url|max:255',
            'city'                 => 'required|string|max:255',
            'state'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'logo'                 => 'nullable|string|max:255',
            'status'               => 'nullable|in:active,inactive,terminated',
            'is_featured'          => 'nullable|boolean',
        ];
    }
}
