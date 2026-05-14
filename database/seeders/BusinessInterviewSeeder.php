<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use Illuminate\Database\Seeder;

class BusinessInterviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_INTERVIEW,
            ],
            [
                'title' => 'Behind the Creative Journey',
                'sub_title' => 'Celebrating our community\'s achievements and creative milestones',
                'description' => "GO deeper into the stories behind the artists. Hear firsthand perspectives on creativity, challenges, culture, and the inspiration that drives their work.\n\n• Early inspirations\n• Defining creative challenges\n• Their \"why\" as an artist\n• The role of community\n• Their message to future creators",
                'metadata' => [
                    'card_title' => 'Artist Interview: Behind the Creative Journey'
                ],
                // Unsplash image matching the workspace/tech theme in image_a2eeb7.png
                'image' => 'https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?q=80&w=2070&auto=format&fit=crop',
            ]
        );
    }
}
