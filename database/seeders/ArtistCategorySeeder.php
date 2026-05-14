<?php

namespace Database\Seeders;

use App\Models\ArtistCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArtistCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Retail',
            'Beauty',
            'Wellness',
            'Food & Beverage',
            'Home Services',
            'Visual Artists',
            'Music & Performance',
            'Digital Arts',
        ];

        foreach ($categories as $name) {
            ArtistCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
        }
    }
}
