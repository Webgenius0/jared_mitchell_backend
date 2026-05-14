<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use Illuminate\Database\Seeder;

class WhyOsiExistsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_WHY_EXISTS,
            ],
            [
                'title' => 'Why OSI Exists',
                'sub_title' => 'Because visibility matters. Because support changes lives. Because community creates opportunity. OSI exists to break the cycle of being overlooked and to replace it with recognition',
            ]
        );
    }
}
