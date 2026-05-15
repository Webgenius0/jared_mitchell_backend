<?php

namespace Database\Factories;

use App\Models\BusinessSpotlight;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessSpotlightFactory extends Factory
{
    protected $model = BusinessSpotlight::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft', 'submitted', 'under_review', 'approved', 'rejected']);
        $submittedAt = $status !== 'draft' ? $this->faker->dateTimeBetween('-1 month', 'now') : null;

        return [
            // Step 1 – Business Information
            'business_name' => $this->faker->company(),
            'owner_founder_name' => $this->faker->name(),
            'business_category' => $this->faker->randomElement(['Technology', 'Fashion', 'Food & Beverage', 'Health', 'Education']),
            'year_founded' => $this->faker->year(),
            'business_website' => $this->faker->url(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),

            // Step 2 – Business Story
            'business_story' => $this->faker->paragraphs(3, true),
            'products_services' => $this->faker->sentences(3, true),
            'challenges_overcome' => $this->faker->sentences(2, true),
            'unique_factor' => $this->faker->sentence(),
            'target_customer' => $this->faker->sentence(),

            // Step 3 – Contact Information
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'best_contact_time' => $this->faker->randomElement(['Morning', 'Afternoon', 'Evening']),
            'instagram_url' => 'https://instagram.com/' . $this->faker->userName(),
            'tiktok_url' => 'https://tiktok.com/@' . $this->faker->userName(),
            'facebook_url' => 'https://facebook.com/' . $this->faker->userName(),
            'youtube_url' => 'https://youtube.com/@' . $this->faker->userName(),
            'google_business_profile_url' => $this->faker->url(),
            'linkedin_url' => $this->faker->url(),
            'fanbase_url' => $this->faker->url(),

            // Step 4 – Images
            'portrait_photo_path' => 'https://placehold.co/400x600?text=Portrait',
            'storefront_workspace_photo_path' => 'https://placehold.co/800x450?text=Storefront',
            'product_service_photo_paths' => [
                'https://placehold.co/600x400?text=Product+1',
                'https://placehold.co/600x400?text=Product+2',
            ],
            'team_photo_path' => 'https://placehold.co/800x450?text=Team',

            // Step 5 – Service Details
            'service_type' => $this->faker->randomElement(['in_person_only', 'online_only', 'both_in_person_and_online']),

            // Step 6 – Spotlight Consideration
            'why_featured' => $this->faker->paragraph(),
            'growth_vision' => $this->faker->paragraph(),
            'permission_feature_on_osi' => true,
            'permission_use_submitted_photos' => true,
            'permission_share_business_story' => true,

            // Submission tracking
            'status' => $status,
            'current_step' => 6,
            'submitted_at' => $submittedAt,
            'reviewed_by' => in_array($status, ['approved', 'rejected', 'under_review']) ? User::role('admin')->first()?->id : null,
            'reviewer_notes' => in_array($status, ['approved', 'rejected']) ? $this->faker->sentence() : null,
        ];
    }
}
