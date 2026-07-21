<?php

namespace Database\Factories;

use App\Models\ArtistCategory;
use App\Models\ArtistSpotlight;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtistSpotlightFactory extends Factory
{
    protected $model = ArtistSpotlight::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft', 'submitted', 'under_review', 'approved', 'rejected']);
        $submittedAt = $status !== 'draft' ? $this->faker->dateTimeBetween('-1 month', 'now') : null;

        return [
            // Link to a user with the 'artist' role (API guard)
            'user_id' => User::role('artist')
                ->inRandomOrder()
                ->first()?->id
                ?? User::factory()->create()->id,

            // Identification
            'full_legal_name' => $this->faker->name(),
            'artist_stage_name' => $this->faker->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->date(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'instagram_handle' => '@' . $this->faker->userName(),
            'tiktok_handle' => '@' . $this->faker->userName(),
            'facebook_url' => 'https://facebook.com/' . $this->faker->userName(),
            'youtube_url' => 'https://youtube.com/@' . $this->faker->userName(),
            'website_portfolio_url' => $this->faker->url(),

            // Category
            'artist_category_id' => ArtistCategory::inRandomOrder()->first()?->id ?? ArtistCategory::factory(),
            'category_other_description' => null,

            // Story
            'short_bio' => $this->faker->sentence(),
            'full_artist_story' => $this->faker->paragraphs(3, true),
            'why_spotlighted' => $this->faker->paragraph(),
            'community_message' => $this->faker->sentence(),
            'current_goals' => $this->faker->sentence(),

            // Media
            'headshot_path' => 'https://placehold.co/400x600?text=Artist+Headshot',
            'artwork_photo_paths' => [
                'https://placehold.co/600x400?text=Artwork+1',
                'https://placehold.co/600x400?text=Artwork+2',
            ],
            'behind_scenes_photo_path' => 'https://placehold.co/800x450?text=Behind+Scenes',
            'intro_video_path' => null,

            // Consent
            'consent_public_release' => true,
            'consent_ownership_declaration' => true,
            'consent_interview_permission' => true,

            // Optional
            'talent_manager_contact' => $this->faker->name(),
            'agent_contact' => $this->faker->name(),
            'press_kit_url' => $this->faker->url(),
            'previous_interviews' => $this->faker->sentence(),
            'awards_recognition' => $this->faker->sentence(),
            'preferred_pronouns' => $this->faker->randomElement(['He/Him', 'She/Her', 'They/Them']),
            'preferred_contact_method' => $this->faker->randomElement(['Email', 'Phone', 'Instagram']),
            'interview_availability' => $this->faker->randomElement(['Weekdays', 'Weekends']),

            // Submission tracking
            'status' => $status,
            'current_step' => 6,
            'submitted_at' => $submittedAt,
            'reviewed_by' => in_array($status, ['approved', 'rejected', 'under_review']) ? User::role('admin')->first()?->id : null,
            'reviewer_notes' => in_array($status, ['approved', 'rejected']) ? $this->faker->sentence() : null,
        ];
    }
}
