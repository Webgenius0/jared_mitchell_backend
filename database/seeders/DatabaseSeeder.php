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
            BusinessSpotlightPageSeeder::class,
            ArtistSpotlightPageSeeder::class,
            EventSeeder::class,
            FAQSeeder::class,
            PricingSeeder::class,
            BusinessSpotlightSeeder::class,
            ArtistSpotlightSeeder::class,
            ServicePageSeeder::class,
            AboutPageSeeder::class,
        ]);
    }
}
