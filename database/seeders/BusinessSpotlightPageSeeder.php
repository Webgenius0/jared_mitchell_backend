<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use Illuminate\Database\Seeder;

class BusinessSpotlightPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'page'    => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_HERO,
                'data'    => [
                    'title'     => 'Local Business Spotlights',
                    'sub_title' => 'Celebrating the entrepreneurs, small businesses, and community leaders shaping our neighborhoods.',
                    'image'     => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=2084&auto=format&fit=crop',
                ],
            ],
            [
                'page'    => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_VIDEO,
                'data'    => [
                    'title'       => 'Watch Our Story',
                    'sub_title'   => 'Learn how we empower local entrepreneurs and community leaders.',
                    'description' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'image'       => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=2072&auto=format&fit=crop',
                ],
            ],
            [
                'page'    => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_LIST,
                'data'    => [
                    'title'     => 'Discover More Business',
                    'sub_title' => 'Meet the businesses shaping our neighborhoods. From innovative startups to community anchors, these stories highlight the courage, creativity, and commitment behind every brand.',
                ],
            ],
            [
                'page'    => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_HIGHLIGHTS,
                'data'    => [
                    'title'     => 'Past Six Months Highlights',
                    'sub_title' => "Celebrating our community's achievements and creative milestones",
                ],
            ],
            [
                'page'    => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_PICKS,
                'data'    => [
                    'title'     => "Editor's Picks",
                    'sub_title' => "Celebrating our community's achievements and creative milestones",
                ],
            ],
            [
                'page'    => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_LADDER,
                'data'    => [
                    'title'     => 'OSI Spotlight Ladder',
                    'sub_title' => 'Community-driven weekly recognition',
                ],
            ],
            [
                'page'    => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_JOIN,
                'data'    => [
                    'title' => 'Become part of a growing network that celebrates art, business, and community.',
                ],
            ],
            [
                'page'    => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_INTERVIEW,
                'data'    => [
                    'title'       => 'Behind the Creative Journey',
                    'sub_title'   => "Celebrating our community's achievements and creative milestones",
                    'description' => "GO deeper into the stories behind the artists. Hear firsthand perspectives on creativity, challenges, culture, and the inspiration that drives their work.\n\n• Early inspirations\n• Defining creative challenges\n• Their \"why\" as an artist\n• The role of community\n• Their message to future creators",
                    'metadata'    => [
                        'card_title' => 'Artist Interview: Behind the Creative Journey',
                    ],
                    'image'       => 'https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?q=80&w=2070&auto=format&fit=crop',
                ],
            ],
            [
                'page'    => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_WHY_EXISTS,
                'data'    => [
                    'title'     => 'Why OSI Exists',
                    'sub_title' => 'Because visibility matters. Because support changes lives. Because community creates opportunity. OSI exists to break the cycle of being overlooked and to replace it with recognition',
                ],
            ],
        ];

        foreach ($sections as $section) {
            CMS::updateOrCreate(
                [
                    'page'    => $section['page'],
                    'section' => $section['section'],
                ],
                $section['data']
            );
        }
    }
}
