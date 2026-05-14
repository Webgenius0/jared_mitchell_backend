<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use Illuminate\Database\Seeder;

class BusinessSpotlightVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_VIDEO,
            ],
            [
                'title' => 'Watch Our Story',
                'sub_title' => 'Learn how we empower local entrepreneurs and community leaders.',
                'description' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=2072&auto=format&fit=crop',
            ]
        );
    }
}
