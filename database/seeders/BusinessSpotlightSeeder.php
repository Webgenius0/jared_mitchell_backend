<?php

namespace Database\Seeders;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Models\CMS;
use Illuminate\Database\Seeder;

class BusinessSpotlightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_HERO,
            ],
            [
                'title' => 'Local Business Spotlights',
                'sub_title' => 'Celebrating the entrepreneurs, small businesses, and community leaders shaping our neighborhoods.',
                'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=2084&auto=format&fit=crop',
            ]
        );
    }
}
