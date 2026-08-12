<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BusinessSpotlightRequest extends FormRequest
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
            // Step 1 – Business Information (Required)
            'business_name' => 'required|string|max:255',
            'owner_founder_name' => 'required|string|max:255',
            'business_category' => 'required|string|max:255',
            'year_founded' => 'nullable|integer|min:1800|max:' . date('Y'),
            'business_website' => 'nullable|url|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',

            // Step 2 – Business Story
            'business_story' => 'nullable|string|max:500',
            'products_services' => 'nullable|string|max:1000',
            'challenges_overcome' => 'nullable|string|max:1000',
            'unique_factor' => 'nullable|string|max:1000',
            'target_customer' => 'nullable|string|max:1000',

            // Step 3 – Contact Information
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'best_contact_time' => 'nullable|string|in:morning,afternoon,evening',

            // Social Media Links
            'instagram_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'google_business_profile_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'fanbase_url' => 'nullable|url|max:255',

            // Step 4 – Images
            'portrait_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'storefront_workspace_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'product_service_photos' => 'nullable|array|max:10',
            'product_service_photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'team_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

            // Step 5 – Service Details
            'service_type' => 'required|in:in_person_only,online_only,both_in_person_and_online',

            // Step 6 – Spotlight Consideration
            'why_featured' => 'nullable|string|max:500',
            'growth_vision' => 'nullable|string|max:500',
            'permission_feature_on_osi' => 'required|boolean',
            'permission_use_submitted_photos' => 'required|boolean',
            'permission_share_business_story' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Step 1
            'business_name.required' => 'Business name is required.',
            'owner_founder_name.required' => 'Owner/Founder name is required.',
            'business_category.required' => 'Business category is required.',
            'year_founded.min' => 'Year founded must be a valid year.',
            'year_founded.max' => 'Year founded cannot be in the future.',
            'business_website.url' => 'Please enter a valid website URL.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',

            // Step 2
            'business_story.max' => 'Business story cannot exceed 500 characters.',
            'products_services.max' => 'Products/services description cannot exceed 1000 characters.',
            'challenges_overcome.max' => 'Challenges description cannot exceed 1000 characters.',

            // Step 3
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'best_contact_time.in' => 'Best contact time must be morning, afternoon, or evening.',

            // Social URLs
            'instagram_url.url' => 'Please enter a valid Instagram URL.',
            'tiktok_url.url' => 'Please enter a valid TikTok URL.',
            'facebook_url.url' => 'Please enter a valid Facebook URL.',
            'youtube_url.url' => 'Please enter a valid YouTube URL.',
            'google_business_profile_url.url' => 'Please enter a valid Google Business Profile URL.',
            'linkedin_url.url' => 'Please enter a valid LinkedIn URL.',
            'fanbase_url.url' => 'Please enter a valid Fanbase URL.',

            // Step 4
            'portrait_photo.required' => 'A portrait photo of the business owner is required.',
            'portrait_photo.image' => 'Portrait photo must be an image.',
            'portrait_photo.mimes' => 'Portrait photo must be a JPEG, PNG, JPG, or WebP file.',
            'portrait_photo.max' => 'Portrait photo must not exceed 5MB.',
            'storefront_workspace_photo.required' => 'A storefront or workspace photo is required.',
            'storefront_workspace_photo.image' => 'Storefront/workspace photo must be an image.',
            'storefront_workspace_photo.mimes' => 'Storefront/workspace photo must be a JPEG, PNG, JPG, or WebP file.',
            'storefront_workspace_photo.max' => 'Storefront/workspace photo must not exceed 5MB.',
            'product_service_photos.max' => 'You can upload a maximum of 10 product/service photos.',
            'product_service_photos.*.image' => 'Each product/service photo must be an image.',
            'product_service_photos.*.mimes' => 'Product/service photos must be JPEG, PNG, JPG, or WebP files.',
            'product_service_photos.*.max' => 'Each product/service photo must not exceed 5MB.',
            'team_photo.image' => 'Team photo must be an image.',
            'team_photo.mimes' => 'Team photo must be a JPEG, PNG, JPG, or WebP file.',
            'team_photo.max' => 'Team photo must not exceed 5MB.',

            // Step 5
            'service_type.required' => 'Service type is required.',
            'service_type.in' => 'Service type must be in-person only, online only, or both.',

            // Step 6
            'why_featured.max' => 'Reason for feature cannot exceed 500 characters.',
            'growth_vision.max' => 'Growth vision cannot exceed 500 characters.',
            'permission_feature_on_osi.required' => 'Please confirm if we can feature your business on OSI.',
            'permission_use_submitted_photos.required' => 'Please confirm if we can use your submitted photos.',
            'permission_share_business_story.required' => 'Please confirm if we can share your business story.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'owner_founder_name' => 'owner/founder name',
            'business_website' => 'business website',
            'phone_number' => 'phone number',
            'best_contact_time' => 'best contact time',
            'instagram_url' => 'Instagram URL',
            'tiktok_url' => 'TikTok URL',
            'facebook_url' => 'Facebook URL',
            'youtube_url' => 'YouTube URL',
            'google_business_profile_url' => 'Google Business Profile URL',
            'linkedin_url' => 'LinkedIn URL',
            'fanbase_url' => 'Fanbase URL',
            'portrait_photo' => 'portrait photo',
            'storefront_workspace_photo' => 'storefront/workspace photo',
            'product_service_photos' => 'product/service photos',
            'team_photo' => 'team photo',
            'service_type' => 'service type',
            'why_featured' => 'reason for feature',
            'growth_vision' => 'growth vision',
            'permission_feature_on_osi' => 'OSI feature permission',
            'permission_use_submitted_photos' => 'photo usage permission',
            'permission_share_business_story' => 'story sharing permission',
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
            'data' => null,
            'errors' => $validator->errors(),
            'code' => 422,
        ], 422));
    }
}
