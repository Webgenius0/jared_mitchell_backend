<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
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
        $businessId = $this->route('business');

        return [
            'business_category_id' => 'nullable|exists:business_categories,id',
            'owner_name'           => 'nullable|string|max:255',
            'business_name'        => 'nullable|string|max:255',
            'slug'                 => 'nullable|string|max:255|unique:businesses,slug,' . $businessId,
            'year_founded'         => 'nullable|integer|min:1800|max:' . date('Y'),
            'website'              => 'nullable|string|url|max:255',
            'city'                 => 'nullable|string|max:255',
            'state'                => 'nullable|string|max:255',
            'description'          => 'nullable|string',
            'logo'                 => 'nullable|string|max:255',
            'status'               => 'nullable|in:active,inactive,terminated',
            'is_featured'          => 'nullable|boolean',
        ];
    }
}
