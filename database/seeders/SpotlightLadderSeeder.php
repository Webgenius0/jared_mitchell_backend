<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use Illuminate\Database\Seeder;

class SpotlightLadderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CMS::updateOrCreate(
            [
                'page' => CmsPage::SPOTLIGHT_LADDER,
                'section' => CmsSection::SPOTLIGHT_LADDER_HERO,
            ],
            [
                'title' => 'Weekly Spotlight Ladder',
                'sub_title' => 'Community-driven recognition for outstanding developers',
                'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=2084&auto=format&fit=crop',
            ]
        );
    }
}
