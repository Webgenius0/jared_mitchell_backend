<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Faker\Factory as Faker;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            // Create a user for the business if needed, or use an existing one
            // Here we create a new user for each business using the User factory.
            // If User factory is not available, you might need to adjust this.
            $user = User::factory()->create();
            $businessName = $faker->company;

            DB::table('businesses')->insert([
                'user_id' => $user->id,
                'owner_name' => $faker->name,
                'business_name' => $businessName,
                'slug' => Str::slug($businessName) . '-' . uniqid(),
                'owner_founder_name' => $faker->name,
                'story' => $faker->paragraphs(3, true),
                'mission' => $faker->paragraph,
                'website_social_media' => json_encode(['website' => $faker->url, 'twitter' => $faker->url]),
                'community_impact_statement' => $faker->paragraph,
                'revenue_stage' => $faker->randomElement(['pre-revenue', '0-10k', '10k-50k', '50k-100k', '100k+']),
                'why_they_deserve_to_compete' => $faker->paragraph,
                'photo_video' => $faker->imageUrl(),
                'status' => $faker->randomElement(['active', 'inactive']),
                'is_featured' => $faker->boolean(20),
                'total_claps' => $faker->numberBetween(0, 1000),
                'total_saves' => $faker->numberBetween(0, 500),
                'total_shares' => $faker->numberBetween(0, 300),
                'total_points' => $faker->numberBetween(0, 5000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
