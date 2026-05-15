<?php

namespace Database\Seeders;

use App\Models\BusinessSpotlight;
use Illuminate\Database\Seeder;

class BusinessSpotlightSeeder extends Seeder
{
    public function run(): void
    {
        BusinessSpotlight::factory()->count(10)->create();
    }
}
