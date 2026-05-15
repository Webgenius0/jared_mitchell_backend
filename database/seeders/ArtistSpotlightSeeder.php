<?php

namespace Database\Seeders;

use App\Models\ArtistSpotlight;
use Illuminate\Database\Seeder;

class ArtistSpotlightSeeder extends Seeder
{
    public function run(): void
    {
        ArtistSpotlight::factory()->count(10)->create();
    }
}
