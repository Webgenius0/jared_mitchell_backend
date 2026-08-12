<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class ArtistSpotlightRequest extends FormRequest
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
        $minAgeDate = Carbon::now()->subYears(18)->format('Y-m-d');

        return [
            // Step 1 – Artist Identification
            'full_legal_name' => 'required|string|max:255',
            'artist_stage_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before_or_equal:' . $minAgeDate,
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',

            // Social Media
            'instagram_handle' => 'nullable|string|max:255',
            'tiktok_handle' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'website_portfolio_url' => 'nullable|url|max:255',

            // Step 2 – Artist Category
            'artist_category_id' => 'required|exists:artist_categories,id',
            'category_other_description' => 'required_if:artist_category_id,other|nullable|string|max:255',

            // Step 3 – Artist Story
            'short_bio' => 'nullable|string|max:500',
            'full_artist_story' => 'nullable|string|max:5000',
            'why_spotlighted' => 'nullable|string|max:2000',
            'community_message' => 'nullable|string|max:2000',
            'current_goals' => 'nullable|string|max:2000',

            // Step 4 – Media Uploads
            'headshot' => 'required|image|mimes:jpeg,png,jpg,webp,heic|max:153600', // 150MB as per migration comment
            'artwork_photos' => 'required|array|min:3|max:5',
            'artwork_photos.*' => 'image|mimes:jpeg,png,jpg,webp,heic|max:153600',
            'behind_scenes_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,heic|max:153600',
            'intro_video' => 'nullable|mimetypes:video/mp4,video/quicktime|max:153600', // MP4, MOV

            // Step 5 – Consent & Rights
            'consent_public_release' => 'required|boolean|accepted',
            'consent_ownership_declaration' => 'required|boolean|accepted',
            'consent_interview_permission' => 'required|boolean|accepted',

            // Step 6 – Optional Information
            'talent_manager_contact' => 'nullable|string|max:255',
            'agent_contact' => 'nullable|string|max:255',
            'press_kit_url' => 'nullable|url|max:255',
            'previous_interviews' => 'nullable|string|max:5000',
            'awards_recognition' => 'nullable|string|max:5000',
            'preferred_pronouns' => 'nullable|string|max:50',
            'preferred_contact_method' => 'nullable|string|max:100',
            'interview_availability' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old to submit a spotlight.',
            'artwork_photos.min' => 'Please upload at least 3 photos of your artwork.',
            'artwork_photos.max' => 'You can upload up to 5 photos of your artwork.',
            'consent_public_release.accepted' => 'You must consent to the public release agreement.',
            'consent_ownership_declaration.accepted' => 'You must declare ownership of your work.',
            'consent_interview_permission.accepted' => 'You must grant permission for an interview.',
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
