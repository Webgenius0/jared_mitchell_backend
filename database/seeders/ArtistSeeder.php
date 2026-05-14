<?php

namespace Database\Seeders;

use App\Helpers\Helper;
use App\Models\ArtistCategory;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Spatie\Permission\Models\Role;

class ArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $categories = ArtistCategory::all();

        if ($categories->isEmpty()) {
            $this->call(ArtistCategorySeeder::class);
            $categories = ArtistCategory::all();
        }

        for ($i = 1; $i <= 10; $i++) {
            $name = $faker->name;
            $email = "artist{$i}@example.com";

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'artist_category_id' => $categories->random()->id,
                ]
            );

            // Assign artist role
            $role = Role::findByName('artist', 'api');
            $user->assignRole($role);

            $slug = Helper::generateSlug($name);
            $username = Helper::generateUsername($name);

            Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $name,
                    'username' => $username,
                    'slug' => $slug,
                    'biography' => $faker->paragraph(2),
                    'tagline' => $faker->sentence,
                    'address' => $faker->address,
                ]
            );
        }
    }
}
