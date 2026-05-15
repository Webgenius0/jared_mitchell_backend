<?php

namespace Database\Seeders;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Models\CMS;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. About Hero
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_HERO],
            [
                'title' => 'Empowering the Creative Spirit',
                'sub_title' => 'The OSI Story: A Journey of Innovation and Community',
                'bg' => 'https://placehold.co/1920x1080?text=About+Hero',
            ]
        );

        // 2. About Society
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_SOCIETY],
            [
                'title' => 'A Vibrant Creative Society',
                'description' => 'OSI is more than just a platform; it is a community of artists, entrepreneurs, and visionaries working together.',
                'image' => 'https://placehold.co/800x600?text=OSI+Society',
            ]
        );

        // 3. About Origin
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_ORIGIN],
            [
                'title' => 'How We Started',
                'description' => 'Founded with a simple goal: to provide artists with the resources they need to succeed in a digital world.',
                'image' => 'https://placehold.co/800x600?text=Our+Origin',
            ]
        );

        // 4. Mission & Purpose
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_MISSION],
            [
                'title' => 'Our Mission & Purpose',
                'metadata' => [
                    [
                        'title' => 'Empowerment',
                        'description' => 'Providing the tools for artists to take control of their careers.',
                        'image' => 'https://placehold.co/400x300?text=Mission+1',
                    ],
                    [
                        'title' => 'Innovation',
                        'description' => 'Constantly evolving our platform to meet the needs of creators.',
                        'image' => 'https://placehold.co/400x300?text=Mission+2',
                    ],
                ],
            ]
        );

        // 5. What We Do
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_WHAT_WE_DO],
            [
                'title' => 'What We Do',
                'metadata' => [
                    [
                        'title' => 'Spotlight Submissions',
                        'description' => 'Giving creators the chance to be featured on a global stage.',
                        'icon' => 'ri-flashlight-line',
                        'image' => 'https://placehold.co/400x300?text=What+We+Do+1',
                    ],
                    [
                        'title' => 'Brand Growth',
                        'description' => 'Helping businesses build their presence through targeted services.',
                        'icon' => 'ri-line-chart-line',
                        'image' => 'https://placehold.co/400x300?text=What+We+Do+2',
                    ],
                ],
            ]
        );

        // 6. How It Works
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_HOW_IT_WORKS],
            [
                'title' => 'The OSI Workflow',
                'sub_title' => 'Simple steps to success',
                'metadata' => [
                    [
                        'title' => 'Sign Up',
                        'description' => 'Create your profile and join the community.',
                        'icon' => 'ri-user-add-line',
                        'image' => 'https://placehold.co/400x300?text=How+It+Works+1',
                    ],
                    [
                        'title' => 'Submit Story',
                        'description' => 'Share your journey with us and get spotlighted.',
                        'icon' => 'ri-edit-line',
                        'image' => 'https://placehold.co/400x300?text=How+It+Works+2',
                    ],
                ],
            ]
        );

        // 7. Who We Serve
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_WHO_WE_SERVE],
            [
                'title' => 'Who We Serve',
                'description' => 'Our platform is designed for everyone from the solo artist to the growing enterprise.',
                'image' => 'https://placehold.co/800x600?text=Who+We+Serve',
            ]
        );

        // 8. Why OSI Exists
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_WHY_EXISTS],
            [
                'title' => 'Why OSI Exists',
                'description' => 'Because the creative industry is underserved, and we are here to change that.',
            ]
        );

        // 9. Our Impact
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_OUR_IMPACT],
            [
                'title' => 'Our Global Impact',
                'sub_title' => 'Numbers that tell our story',
                'metadata' => [
                    '5,000+ Artists Supported',
                    '200+ Businesses Featured',
                    '50+ Countries Global Reach',
                ],
            ]
        );

        // 10. Founder Message
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_FOUNDER_MESSAGE],
            [
                'title' => 'A Word from Our Founders',
                'metadata' => [
                    [
                        'name' => 'Jared Mitchell',
                        'designation' => 'CEO & Founder',
                        'message' => 'Our vision is to create a world where every artist has the opportunity to thrive.',
                        'sub_label' => 'Leading with Passion',
                        'image' => 'https://placehold.co/400x500?text=Jared+Mitchell',
                    ],
                ],
            ]
        );

        // 11. Join Section
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_JOIN],
            [
                'title' => 'Ready to Join the Movement?',
            ]
        );

        // 12. Newsletter Section
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_NEWSLETTER],
            [
                'title' => 'Subscribe for Exclusive Updates',
            ]
        );

        // 13. Sponsors Section
        CMS::updateOrCreate(
            ['page' => CmsPage::ABOUT, 'section' => CmsSection::ABOUT_SPONSORS],
            [
                'title' => 'Our Official Sponsors',
                'metadata' => [
                    ['image' => 'https://placehold.co/200x100?text=Sponsor+1', 'link' => 'https://sponsor1.com'],
                    ['image' => 'https://placehold.co/200x100?text=Sponsor+2', 'link' => 'https://sponsor2.com'],
                    ['image' => 'https://placehold.co/200x100?text=Sponsor+3', 'link' => 'https://sponsor3.com'],
                ],
            ]
        );
    }
}
