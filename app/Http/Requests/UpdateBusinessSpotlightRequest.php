<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBusinessSpotlightRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules — every field is nullable so partial updates work.
     * Photo fields accept a new upload but are not required (existing files kept if omitted).
     */
    public function rules(): array
    {
        return [
            // Step 1 – Business Information
            'business_name'       => 'nullable|string|max:255',
            'owner_founder_name'  => 'nullable|string|max:255',
            'business_category'   => 'nullable|string|max:255',
            'year_founded'        => 'nullable|integer|min:1800|max:' . date('Y'),
            'business_website'    => 'nullable|url|max:255',
            'city'                => 'nullable|string|max:255',
            'state'               => 'nullable|string|max:255',

            // Step 2 – Business Story
            'business_story'      => 'nullable|string|max:500',
            'products_services'   => 'nullable|string|max:1000',
            'challenges_overcome' => 'nullable|string|max:1000',
            'unique_factor'       => 'nullable|string|max:1000',
            'target_customer'     => 'nullable|string|max:1000',

            // Step 3 – Contact Information
            'email'               => 'nullable|email|max:255',
            'phone_number'        => 'nullable|string|max:20',
            'best_contact_time'   => 'nullable|string|in:morning,afternoon,evening',

            // Social Media Links
            'instagram_url'               => 'nullable|url|max:255',
            'tiktok_url'                  => 'nullable|url|max:255',
            'facebook_url'                => 'nullable|url|max:255',
            'youtube_url'                 => 'nullable|url|max:255',
            'google_business_profile_url' => 'nullable|url|max:255',
            'linkedin_url'                => 'nullable|url|max:255',
            'fanbase_url'                 => 'nullable|url|max:255',

            // Step 4 – Images (optional on update — omit to keep existing file)
            'portrait_photo'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'storefront_workspace_photo'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'product_service_photos'      => 'nullable|array|max:10',
            'product_service_photos.*'    => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'team_photo'                  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

            // Step 5 – Service Details
            'service_type'  => 'nullable|in:in_person_only,online_only,both_in_person_and_online',

            // Step 6 – Spotlight Consideration
            'why_featured'                    => 'nullable|string|max:500',
            'growth_vision'                   => 'nullable|string|max:500',
            'permission_feature_on_osi'       => 'nullable|boolean',
            'permission_use_submitted_photos' => 'nullable|boolean',
            'permission_share_business_story' => 'nullable|boolean',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => implode(' ', $validator->errors()->all()) ?: 'Validation failed',
            'data'    => null,
            'errors'  => $validator->errors(),
            'code'    => 422,
        ], 422));
    }
}
