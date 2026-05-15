<?php

namespace Database\Seeders;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Models\CMS;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Hero
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::HERO],
            [
                'title' => 'Redefining the Creator Economy',
                'sub_title' => 'Empowering Artists and Businesses Worldwide',
                'description' => 'Join the largest network of creative professionals and scale your vision with OSI.',
                'video' => 'https://www.w3schools.com/html/mov_bbb.mp4', // Sample video
            ]
        );

        // 2. Partners
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::PARTNERS],
            [
                'title' => 'Our Global Partners',
                'metadata' => [
                    ['image' => 'https://placehold.co/200x100?text=Partner+A', 'link' => 'https://partnera.com'],
                    ['image' => 'https://placehold.co/200x100?text=Partner+B', 'link' => 'https://partnerb.com'],
                    ['image' => 'https://placehold.co/200x100?text=Partner+C', 'link' => 'https://partnerc.com'],
                    ['image' => 'https://placehold.co/200x100?text=Partner+D', 'link' => 'https://partnerd.com'],
                ],
            ]
        );

        // 3. Features
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::FEATURES],
            [
                'title' => 'Why OSI is Different',
                'description' => 'We combine cutting-edge technology with a deep understanding of the creative process.',
                'bg' => 'https://placehold.co/1920x1080?text=Features+BG',
            ]
        );

        // 4. Why Choose
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::WHY_CHOOSE],
            [
                'title' => 'Why Choose OSI?',
                'sub_title' => 'The platform built for your success',
                'metadata' => [
                    [
                        'title' => 'Global Reach',
                        'sub_title' => 'Expand your horizons',
                        'description' => 'Connect with audiences and businesses across the globe.',
                        'image' => 'https://placehold.co/400x300?text=Global+Reach',
                    ],
                    [
                        'title' => 'Dedicated Support',
                        'sub_title' => 'We are here for you',
                        'description' => '24/7 support to ensure your projects stay on track.',
                        'image' => 'https://placehold.co/400x300?text=Support',
                    ],
                ],
            ]
        );

        // 5. Core Values
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::CORE_VALUES],
            [
                'title' => 'Our Core Values',
                'bg' => 'https://placehold.co/1920x1080?text=Core+Values+BG',
                'metadata' => [
                    [
                        'icon' => 'ri-heart-line',
                        'title' => 'Passion',
                        'sub_title' => 'Driven by creativity',
                        'description' => 'We love what we do and it shows in every project.',
                    ],
                    [
                        'icon' => 'ri-lightbulb-line',
                        'title' => 'Innovation',
                        'sub_title' => 'Thinking outside the box',
                        'description' => 'Always looking for new ways to solve old problems.',
                    ],
                ],
            ]
        );

        // 6. What You Get
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::WHAT_YOU_GET],
            [
                'title' => 'What You Really Getting',
                'sub_title' => 'Everything you need to thrive',
                'metadata' => [
                    ['icon' => 'ri-check-line', 'title' => 'Full Profile Access'],
                    ['icon' => 'ri-check-line', 'title' => 'Priority Spotlight'],
                    ['icon' => 'ri-check-line', 'title' => 'Expert Mentorship'],
                    ['icon' => 'ri-check-line', 'title' => 'Exclusive Events'],
                ],
            ]
        );

        // 7. Boss Beginnings
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::BOSS_BEGINNINGS],
            [
                'title' => 'Boss Beginnings',
                'sub_title' => 'Where it all started',
                'description' => 'Our journey from a small studio to a global powerhouse.',
                'image' => 'https://placehold.co/800x600?text=Boss+Beginnings',
            ]
        );

        // 8. Spotlight Section
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::SPOTLIGHT],
            [
                'title' => 'Artist Spotlight',
                'sub_title' => 'Discover the next big thing',
            ]
        );

        // 9. Highlights
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::HIGHLIGHTS],
            [
                'title' => 'Monthly Highlights',
                'sub_title' => 'The best of OSI this month',
            ]
        );

        // 10. Events
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::EVENTS],
            [
                'title' => 'Upcoming OSI Events',
                'description' => 'Don\'t miss out on our workshops, seminars, and networking nights.',
                'bg' => 'https://placehold.co/1920x1080?text=Events+BG',
            ]
        );

        // 11. Shop
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::SHOP],
            [
                'title' => 'OSI Official Shop',
                'sub_title' => 'Merchandise and digital assets for creators',
            ]
        );

        // 12. CTA
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::CTA],
            [
                'title' => 'Ready to Start Your Journey?',
            ]
        );

        // 13. Newsletter
        CMS::updateOrCreate(
            ['page' => CmsPage::HOME, 'section' => CmsSection::NEWSLETTER],
            [
                'title' => 'Join the OSI Newsletter',
                'sub_title' => 'Get the latest news and offers delivered to your inbox.',
            ]
        );
    }
}
