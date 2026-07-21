<?php

namespace Database\Seeders;

use App\Models\Spotlight\SpotlightVotePackage;
use Illuminate\Database\Seeder;

class SpotlightVotePackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds the default vote packages matching the original hardcoded pricing.
     */
    public function run(): void
    {
        SpotlightVotePackage::create([
            'name'       => 'Starter',
            'slug'       => 'starter',
            'votes_count' => 1,
            'price'      => 1.00,
            'description' => '1 vote — $1.00',
            'is_active'   => true,
            'sort_order'  => 1,
        ]);

        SpotlightVotePackage::create([
            'name'       => 'Popular',
            'slug'       => 'popular',
            'votes_count' => 10,
            'price'      => 8.00,
            'description' => '10 votes — $8.00',
            'is_active'   => true,
            'sort_order'  => 2,
        ]);

        SpotlightVotePackage::create([
            'name'       => 'Boost',
            'slug'       => 'boost',
            'votes_count' => 25,
            'price'      => 18.00,
            'description' => '25 votes — $18.00',
            'is_active'   => true,
            'sort_order'  => 3,
        ]);

        SpotlightVotePackage::create([
            'name'       => 'Power',
            'slug'       => 'power',
            'votes_count' => 50,
            'price'      => 35.00,
            'description' => '50 votes — $35.00',
            'is_active'   => true,
            'sort_order'  => 4,
        ]);
    }
}
