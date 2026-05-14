<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use Illuminate\Database\Seeder;

class BusinessPicksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_PICKS,
            ],
            [
                'title' => "Editor's Picks",
                'sub_title' => "Celebrating our community's achievements and creative milestones",
            ]
        );
    }
}
