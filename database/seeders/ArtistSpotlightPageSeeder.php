<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use Illuminate\Database\Seeder;

class ArtistSpotlightPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'page'    => CmsPage::ARTIST_SPOTLIGHT,
                'section' => CmsSection::ARTIST_SPOTLIGHT_HERO,
                'data'    => [
                    'title'     => 'Artist Spotlights',
                    'sub_title' => 'Discover talented artists shaping the culture through music, visuals, performance, and creativity.',
                    'image'     => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?q=80&w=2064&auto=format&fit=crop',
                ],
            ],
            [
                'page'    => CmsPage::ARTIST_SPOTLIGHT,
                'section' => CmsSection::ARTIST_SPOTLIGHT_VIDEO,
                'data'    => [
                    'title'       => 'Spotlight: Creative Journeys',
                    'sub_title'   => 'Watch the stories and inspirations behind your favorite artists.',
                    'description' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'image'       => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=2072&auto=format&fit=crop',
                ],
            ],
            [
                'page'    => CmsPage::ARTIST_SPOTLIGHT,
                'section' => CmsSection::ARTIST_SPOTLIGHT_LIST,
                'data'    => [
                    'title'     => 'Discover More Artists',
                    'sub_title' => 'Meet the businesses shaping our neighborhoods. From innovative startups to community anchors, these stories highlight the courage, creativity, and commitment behind every brand.',
                ],
            ],
            [
                'page'    => CmsPage::ARTIST_SPOTLIGHT,
                'section' => CmsSection::ARTIST_SPOTLIGHT_HIGHLIGHTS,
                'data'    => [
                    'title'     => 'Past Six Months Highlights',
                    'sub_title' => "Celebrating our community's achievements and creative milestones",
                ],
            ],
            [
                'page'    => CmsPage::ARTIST_SPOTLIGHT,
                'section' => CmsSection::ARTIST_SPOTLIGHT_LADDER,
                'data'    => [
                    'title'     => 'Weekly Spotlight Ladder',
                    'sub_title' => 'Community-driven recognition for outstanding developers',
                ],
            ],
            [
                'page'    => CmsPage::ARTIST_SPOTLIGHT,
                'section' => CmsSection::ARTIST_SPOTLIGHT_JOIN,
                'data'    => [
                    'title'     => 'Become part of a growing network that celebrates art, business, and community.',
                    'sub_title' => null,
                ],
            ],
            [
                'page'    => CmsPage::ARTIST_SPOTLIGHT,
                'section' => CmsSection::ARTIST_SPOTLIGHT_INTERVIEW,
                'data'    => [
                    'title'       => 'Behind the Creative Journey',
                    'sub_title'   => "Celebrating our community's achievements and creative milestones",
                    'description' => 'GO deeper into the stories behind the artists. Hear firsthand perspectives on creativity, challenges, culture, and the inspiration that drives their work.',
                    'metadata'    => [
                        'card_title' => 'Artist Interview: Behind the Creative Journey',
                    ],
                    'image'       => 'https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?q=80&w=2070&auto=format&fit=crop',
                ],
            ],
            [
                'page'    => CmsPage::ARTIST_SPOTLIGHT,
                'section' => CmsSection::ARTIST_SPOTLIGHT_WHY_EXISTS,
                'data'    => [
                    'title'     => 'Why OSI Exists',
                    'sub_title' => 'Because visibility matters. Because support changes lives. Because community creates opportunity. OSI exists to break the cycle of being overlooked — and to replace it with recognition, collaboration, and growth.',
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
