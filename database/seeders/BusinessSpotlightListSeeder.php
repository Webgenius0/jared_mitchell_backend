<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use Illuminate\Database\Seeder;

class BusinessSpotlightListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_LIST,
            ],
            [
                'title' => 'Discover More Business',
                'sub_title' => 'Meet the businesses shaping our neighborhoods. From innovative startups to community anchors, these stories highlight the courage, creativity, and commitment behind every brand.',
            ]
        );
    }
}
