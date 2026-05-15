<?php

namespace Database\Seeders;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Models\CMS;
use Illuminate\Database\Seeder;

class ServicePageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Services Hero
        CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_HERO],
            [
                'title' => 'Elevate Your Vision with OSI Services',
                'image' => 'https://placehold.co/1920x1080?text=Services+Hero',
            ]
        );

        // 2. Services Overview
        CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_OVERVIEW],
            [
                'description' => 'We provide comprehensive tools and platforms for artists and businesses to grow their presence and impact.',
            ]
        );

        // 3. Services Grow
        CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_GROW],
            [
                'title' => 'Grow Your Brand with Expert Guidance',
                'description' => 'Our specialized programs are designed to help you scale effectively in the modern digital landscape.',
                'image' => 'https://placehold.co/800x600?text=Grow+Section',
            ]
        );

        // 4. Services Partners
        CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_PARTNERS],
            [
                'title' => 'Our Trusted Partners',
                'description' => 'Collaborating with industry leaders to bring you the best opportunities.',
                'metadata' => [
                    ['image' => 'https://placehold.co/200x100?text=Partner+1', 'link' => 'https://example.com/1'],
                    ['image' => 'https://placehold.co/200x100?text=Partner+2', 'link' => 'https://example.com/2'],
                    ['image' => 'https://placehold.co/200x100?text=Partner+3', 'link' => 'https://example.com/3'],
                ],
            ]
        );

        // 5. Who OSI Is For
        CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_WHO_FOR],
            [
                'title' => 'Who is OSI For?',
                'sub_title' => 'Tailored solutions for every stage',
                'description' => 'Whether you are just starting out or looking to scale, OSI has the right tools for you.',
                'metadata' => [
                    ['title' => 'Emerging Artists', 'icon' => 'ri-palette-line', 'image' => 'https://placehold.co/400x300?text=Artists'],
                    ['title' => 'Small Businesses', 'icon' => 'ri-briefcase-line', 'image' => 'https://placehold.co/400x300?text=Business'],
                    ['title' => 'Creative Agencies', 'icon' => 'ri-community-line', 'image' => 'https://placehold.co/400x300?text=Agencies'],
                ],
            ]
        );

        // 6. Artist Spotlight Section
        CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_ARTIST_SPOTLIGHT],
            [
                'title' => 'Featured Artists',
                'sub_title' => 'Shining a light on creative excellence',
            ]
        );

        // 7. Business Spotlight Section
        CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_BUSINESS_SPOTLIGHT],
            [
                'title' => 'Business Excellence',
                'sub_title' => 'Highlighting industry innovators',
            ]
        );

        // 8. Newsletter Section
        CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_NEWSLETTER],
            [
                'title' => 'Stay Updated with OSI Newsletter',
            ]
        );

        // 9. Risk Free Section
        CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_RISK_FREE],
            [
                'title' => 'Risk Free Guarantee',
                'sub_title' => 'Your success is our priority',
                'metadata' => [
                    'No long-term contracts required.',
                    'Dedicated support for every client.',
                    'Proven results across multiple industries.',
                    'Flexible plans that grow with you.',
                ],
            ]
        );
    }
}
