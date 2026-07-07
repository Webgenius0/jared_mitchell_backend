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
            'business_name' => 'required|string|max:255',
            'owner_founder_name' => 'nullable|string|max:255',
            'story' => 'nullable|string',
            'mission' => 'nullable|string',
            'website_social_media' => 'nullable|string',
            'community_impact_statement' => 'nullable|string',
            'revenue_stage' => 'nullable|string',
            'why_they_deserve_to_compete' => 'nullable|string',
            'photo_video'   => 'nullable|array',
            'photo_video.*' => 'file|mimes:jpeg,png,jpg,mp4,mov,avi|max:51200',
            'status' => 'nullable|in:active,inactive,terminated',
        ];
    }
}
