<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateArtistSpotlightRequest extends FormRequest
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
     * Media fields accept new uploads but are not required (existing files kept if omitted).
     * File type/size validation is applied only when a file is actually uploaded,
     * so empty or omitted file fields don't cause false validation failures.
     */
    public function rules(): array
    {
        $minAgeDate = Carbon::now()->subYears(18)->format('Y-m-d');

        $rules = [
            // Step 1 – Artist Identification
            'full_legal_name'   => 'nullable|string|max:255',
            'artist_stage_name' => 'nullable|string|max:255',
            'email'             => 'nullable|email|max:255',
            'phone_number'      => 'nullable|string|max:20',
            'date_of_birth'     => 'nullable|date|before_or_equal:' . $minAgeDate,
            'city'              => 'nullable|string|max:255',
            'state'             => 'nullable|string|max:255',

            // Social Media
            'instagram_handle'    => 'nullable|string|max:255',
            'tiktok_handle'       => 'nullable|string|max:255',
            'facebook_url'        => 'nullable|url|max:255',
            'youtube_url'         => 'nullable|url|max:255',
            'website_portfolio_url' => 'nullable|url|max:255',

            // Step 2 – Artist Category
            'artist_category_id'        => 'nullable|exists:artist_categories,id',
            'category_other_description'=> 'nullable|string|max:255',

            // Step 3 – Artist Story
            'short_bio'         => 'nullable|string|max:500',
            'full_artist_story' => 'nullable|string|max:5000',
            'why_spotlighted'   => 'nullable|string|max:2000',
            'community_message' => 'nullable|string|max:2000',
            'current_goals'     => 'nullable|string|max:2000',

            // Step 4 – Media Uploads (conditional — only validate type/size when file is actually uploaded)
            'headshot'              => 'nullable',
            'artwork_photos'        => 'nullable|array|max:5',
            'artwork_photos.*'      => 'nullable',
            'behind_scenes_photo'   => 'nullable',
            'intro_video'           => 'nullable',

            // Step 5 – Consent & Rights
            'consent_public_release'        => 'nullable|boolean',
            'consent_ownership_declaration' => 'nullable|boolean',
            'consent_interview_permission'  => 'nullable|boolean',

            // Step 6 – Optional Information
            'talent_manager_contact'  => 'nullable|string|max:255',
            'agent_contact'           => 'nullable|string|max:255',
            'press_kit_url'           => 'nullable|url|max:255',
            'previous_interviews'     => 'nullable|string|max:5000',
            'awards_recognition'      => 'nullable|string|max:5000',
            'preferred_pronouns'      => 'nullable|string|max:50',
            'preferred_contact_method'=> 'nullable|string|max:100',
            'interview_availability'  => 'nullable|string|max:2000',
        ];

        // Only apply file-type validation rules when a file is actually uploaded.
        // This prevents false validation failures when a field is present in the
        // multipart form but no actual file was selected (common in partial updates).
        if ($this->hasFile('headshot')) {
            $rules['headshot'] = 'nullable|image|mimes:jpeg,png,jpg,webp,heic|max:153600';
        }

        if ($this->hasFile('behind_scenes_photo')) {
            $rules['behind_scenes_photo'] = 'nullable|image|mimes:jpeg,png,jpg,webp,heic|max:153600';
        }

        if ($this->hasFile('intro_video')) {
            $rules['intro_video'] = 'nullable|mimetypes:video/mp4,video/quicktime|max:153600';
        }

        if ($this->hasFile('artwork_photos')) {
            $rules['artwork_photos.*'] = 'image|mimes:jpeg,png,jpg,webp,heic|max:153600';
        }

        return $rules;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'data'    => null,
            'errors'  => $validator->errors(),
            'code'    => 422,
        ], 422));
    }
}
