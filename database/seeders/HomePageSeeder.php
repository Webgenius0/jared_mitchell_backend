<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Section;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'home'],
            [
                'name' => 'Home',
                'meta_title' => 'Home | Our Social Image',
                'meta_description' => 'Welcome to Our Social Image – the platform where creativity meets community.',
                'is_published' => true,
            ]
        );

        // 1. Hero Section
        $hero = $page->sections()->updateOrCreate(
            ['section_key' => 'hero'],
            [
                'label' => 'Hero Section',
                'order' => 1,
                'is_visible' => true,
            ]
        );

        $this->seedContents($hero, [
            ['field_key' => 'headline', 'field_type' => 'text', 'value' => 'Our Social Image'],
            ['field_key' => 'subheading', 'field_type' => 'text', 'value' => 'Where the images of our society'],
            ['field_key' => 'video', 'field_type' => 'video', 'value' => 'cms/hero-video.mp4'],
            ['field_key' => 'description', 'field_type' => 'richtext', 'value' => 'Welcome to Our Social Image – the platform where creativity meets community, from our collective small businesses, artists, and cultural innovators; shaping the world around us. Explore stories, and join a growing network of creators who share local beauty, progress, and purpose. Your insight, our image — and together we create something powerful!'],
            ['field_key' => 'primary_cta_text', 'field_type' => 'text', 'value' => 'Join OSI'],
            ['field_key' => 'primary_cta_url', 'field_type' => 'url', 'value' => '/join'],
            ['field_key' => 'secondary_cta_text', 'field_type' => 'text', 'value' => 'Support Us'],
            ['field_key' => 'secondary_cta_url', 'field_type' => 'url', 'value' => '/support'],
        ]);

        // 2. Community Partners Section
        $partners = $page->sections()->updateOrCreate(
            ['section_key' => 'community_partners'],
            [
                'label' => 'Community Partners Section',
                'order' => 2,
                'is_visible' => true,
            ]
        );

        $this->seedContents($partners, [
            ['field_key' => 'title', 'field_type' => 'text', 'value' => 'POWERED BY OUR COMMUNITY PARTNERS'],
            ['field_key' => 'button_text', 'field_type' => 'text', 'value' => 'Become a Partner'],
            ['field_key' => 'button_url', 'field_type' => 'url', 'value' => '/partner'],
        ]);

        $this->seedItems($partners, [
            ['image' => 'cms/partners/amazon.png'],
            ['image' => 'cms/partners/slack.png'],
            ['image' => 'cms/partners/woocommerce.png'],
            ['image' => 'cms/partners/amazon.png'],
            ['image' => 'cms/partners/slack.png'],
            ['image' => 'cms/partners/slack.png'],
            ['image' => 'cms/partners/amazon.png'],
            ['image' => 'cms/partners/slack.png'],
            ['image' => 'cms/partners/woocommerce.png'],
            ['image' => 'cms/partners/amazon.png'],
        ]);

        // 3. Promo Banner Section
        $promo = $page->sections()->updateOrCreate(
            ['section_key' => 'promo_banner'],
            [
                'label' => 'Promo Banner Section',
                'order' => 3,
                'is_visible' => true,
            ]
        );

        $this->seedContents($promo, [
            ['field_key' => 'headline', 'field_type' => 'text', 'value' => 'Everything You Need to Grow Your Business — Powered by OSI.'],
            ['field_key' => 'subtext', 'field_type' => 'richtext', 'value' => 'Sharing support, stories, and community — Always ensuring authenticity across major platforms. Use OSI to build your brand and expand your audience, with insights into community-led impact, local progress, and cultural influence.'],
        ]);

        // 4. Why Choose OSI Section
        $whyChoose = $page->sections()->updateOrCreate(
            ['section_key' => 'why_choose_osi'],
            [
                'label' => 'Why Choose OSI Section',
                'order' => 4,
                'is_visible' => true,
            ]
        );

        $this->seedContents($whyChoose, [
            ['field_key' => 'title', 'field_type' => 'text', 'value' => 'WHY CHOOSE OSI'],
            ['field_key' => 'subtext', 'field_type' => 'text', 'value' => 'Powering awareness, support, and community growth.'],
        ]);

        $this->seedItems($whyChoose, [
            [
                'title' => 'Creators',
                'heading' => 'Build exposure without chasing algorithms',
                'description' => 'Grow your brand through OSI spotlight features, and campaigns that help your story reach the right audience.',
                'image' => 'cms/why-choose/creator.png',
            ]
        ]);

        // 5. Our Core Values Section
        $coreValues = $page->sections()->updateOrCreate(
            ['section_key' => 'core_values'],
            [
                'label' => 'Our Core Values Section',
                'order' => 5,
                'is_visible' => true,
            ]
        );

        $this->seedContents($coreValues, [
            ['field_key' => 'title', 'field_type' => 'text', 'value' => 'Our Core Values'],
            ['field_key' => 'footer_text', 'field_type' => 'text', 'value' => '*The platform\'s name should be Jared Mitchell, influenced by context, and represented in user-multiple formats. You can find more settings, features, and walkthroughs with the getting-ready guide. There is much to learn about our values and mission for the years to come.'],
        ]);

        $this->seedItems($coreValues, [
            [
                'title' => 'Intentional Visibility',
                'description' => 'Visibility should be meaningful, not random. All highlighted content is selected based on merit, impact, and purpose — prioritizing community value over commercial data.',
                'icon' => 'bi-eye',
            ],
            [
                'title' => 'Community Over Vanity Metrics',
                'description' => 'We support creators through their focus. Build OSI on a track record of impact and trust, not just raw engagement metrics that move for everyone.',
                'icon' => 'bi-people',
            ],
            [
                'title' => 'Accessibility Without Exploitation',
                'description' => 'OSI provides tools and exposure that empower you without compromise. More than a marketplace — we facilitate authentic connection without hidden costs.',
                'icon' => 'bi-unlock',
            ],
            [
                'title' => 'Respect for the Craft',
                'description' => 'Creators and stories are aligned. OSI preserves content authenticity and integrity, ensuring original work is celebrated for how it\'s created.',
                'icon' => 'bi-award',
            ],
            [
                'title' => 'Progress Over Perfection',
                'description' => 'We believe in iterative growth. Every campaign and story shared on OSI is a step toward building a more transparent community through creativity.',
                'icon' => 'bi-graph-up',
            ],
            [
                'title' => 'We Win When You Win',
                'description' => 'Success should be shared. OSI promotes a future where growth and sustainability are continuous — empowering those who build and inspire local ecosystems.',
                'icon' => 'bi-star',
            ],
        ]);
    }

    private function seedContents(Section $section, array $contents): void
    {
        foreach ($contents as $content) {
            $section->contents()->updateOrCreate(
                ['field_key' => $content['field_key'], 'locale' => 'en'],
                [
                    'field_type' => $content['field_type'],
                    'value' => $content['value'],
                ]
            );
        }
    }

    private function seedItems(Section $section, array $items): void
    {
        $section->items()->delete();
        foreach ($items as $index => $item) {
            $section->items()->create([
                'order' => $index + 1,
                'data' => $item,
            ]);
        }
    }
}
