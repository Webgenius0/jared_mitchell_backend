<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            ArtistCategorySeeder::class,
            ArtistSeeder::class,
            SpotlightLadderSeeder::class,
            BusinessSpotlightSeeder::class,
            BusinessSpotlightVideoSeeder::class,
            BusinessSpotlightListSeeder::class,
            BusinessHighlightsSeeder::class,
            BusinessPicksSeeder::class,
            BusinessLadderSeeder::class,
            BusinessJoinSeeder::class,
            BusinessInterviewSeeder::class,
            WhyOsiExistsSeeder::class,
        ]);
    }
}
