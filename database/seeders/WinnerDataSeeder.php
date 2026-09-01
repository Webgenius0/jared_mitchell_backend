<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WinnerDataSeeder extends Seeder
{
    /**
     * Run the database seeds for Boss Beginning Winner & Spotlight Winner data.
     */
    public function run(): void
    {
        // 1. Ensure prerequisite User records exist
        if (\App\Models\User::count() === 0) {
            $this->call(UserSeeder::class);
        }

        // 2. Seed Boss Beginning Winners (creates completed season & winner contestant)
        $this->call(ContestWinnersSeeder::class);

        // 3. Seed Spotlight Winners (creates completed spotlight week & winner nominee)
        $this->call(SpotlightHistoricalWinnersSeeder::class);
    }
}
