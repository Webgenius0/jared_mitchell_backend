<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Clear existing pricing data
            PricingPlan::query()->each(function ($plan) {
                $plan->featureGroups()->each(fn($g) => $g->items()->delete());
                $plan->featureGroups()->delete();
                $plan->delete();
            });

            // 1. BASIC PLAN
            $basic = PricingPlan::create([
                'plan_name' => 'BASIC PLAN',
                'price' => 25,
                'price_suffix' => ' / month',
                'best_for' => 'Beginners, new entrepreneurs, artists, and small businesses needing steady visibility and automated posting at an affordable cost.',
                'outcome_text' => 'Affordable visibility + automated posting + a steady introduction into the OSI ecosystem.',
                'button_label' => 'Get Started',
                'button_url' => '#',
                'is_featured' => false,
                'is_visible' => true,
                'sort_order' => 0,
            ]);

            $this->addGroups($basic, [
                'AI-Automated Social Media Posting' => [
                    '2 days per week',
                    '3 posts per day',
                    'AI-written captions',
                    'Auto-scheduled',
                    'Posted to 1-2 platforms',
                ],
                'OSI Visibility & Brand Tools' => [
                    '1 Spotlight submission per month',
                    'Artist/Business profile on OSI',
                    'Basic AI Market Snapshot (who your audience is, top interests, basic behavior insights)',
                ],
                'Community & Exposure' => [
                    'Access to OSI community features',
                    'Newsletter highlight: Name mentioned in monthly community spotlight',
                ],
                'Bonuses' => [
                    'Access to templates and basic Canva library resources',
                    'Access to OSI event tickets at discounted "member rate"',
                ],
            ]);

            // 2. GROWTH PLAN
            $growth = PricingPlan::create([
                'plan_name' => 'GROWTH PLAN',
                'badge_text' => 'Most Popular',
                'price' => 50,
                'price_suffix' => ' / month',
                'best_for' => 'Growing creators, small business owners, and brands that want more posts, deeper insights, and stronger exposure.',
                'outcome_text' => 'More automation, more eyes on your brand, more growth. AI helps you produce smarter content, reach more people, and get featured more often.',
                'button_label' => 'Get Started',
                'button_url' => '#',
                'is_featured' => true,
                'is_visible' => true,
                'sort_order' => 1,
            ]);

            $this->addGroups($growth, [
                'Everything in Basic, Plus:' => [],
                'AI-Automated Social Media Posting' => [
                    '4 days per week',
                    '3 posts per day',
                    'Multi-platform posting',
                    'Branded content templates',
                    'Caption rewriting + hashtag optimization',
                    'Canva integration with shared OSI template library',
                ],
                'AI Growth Insights (Advanced)' => [
                    'Deep target audience analysis',
                    'Competitor comparisons',
                    'AI suggestion report: "How to attract your audience this month"',
                    'Engagement pattern breakdown',
                ],
                'Spotlight & Promotion' => [
                    'Unlimited Spotlight submissions',
                    'Homepage visibility rotation',
                    'Priority placement in OSI newsletters',
                    'Listed in "Top OSI Creators & Businesses" monthly recap',
                ],
                'Event & Community' => [
                    'Early access to OSI Events',
                    '10% discount on OSI vendor spaces',
                    'Access to OSI network resources + job board',
                ],
            ]);

            // 3. PRO BUSINESS
            $pro = PricingPlan::create([
                'plan_name' => 'PRO BUSINESS',
                'price' => 100,
                'price_suffix' => ' / month',
                'best_for' => 'Brands, entrepreneurs, service providers, and creators ready for maximum exposure, daily AI posting, and OSI\'s full promotional engine.',
                'outcome_text' => 'Your brand becomes a dominant force inside OSI. Automatic posting, premium features, video promotion, top placement, and full AI intelligence systems help you scale fast.',
                'button_label' => 'Get Started',
                'button_url' => '#',
                'is_featured' => false,
                'is_visible' => true,
                'sort_order' => 2,
            ]);

            $this->addGroups($pro, [
                'Everything in Growth, Plus:' => [],
                'AI-Automated Posting (Unlimited)' => [
                    'Unlimited social media posting',
                    '3-5 posts per day',
                    'AI-scheduled across all platforms',
                    'Full Canva template library access',
                    'On-brand custom templates',
                    'Auto-repurposing (turn videos -> clips, text -> posts, etc.)',
                ],
                'Advanced AI Audience Intelligence' => [
                    'Full OSI Market Dashboard',
                    'Behavioral heatmaps',
                    '“Best Posting Time” AI assistant',
                    'Audience demographics + spending patterns',
                    'Monthly audience trend report',
                ],
                'Top-Tier Promotion & Marketing' => [
                    'Premium placement in Spotlight Highlights',
                    'Featured in OSI Video Channels',
                    'Ads promoted across OSI social media',
                    '1 guaranteed feature per month (Artist or Business Spotlight)',
                    'VIP placement during high-traffic cycles (holidays, events, seasonal spikes)',
                ],
                'Event & Partnership Benefits' => [
                    '25% off OSI vendor spaces',
                    'VIP access to OSI Events',
                    'Business Showcase placement on OSI homepage',
                    'Partner dashboard access',
                ],
            ]);
        });
    }

    private function addGroups(PricingPlan $plan, array $groups)
    {
        $gOrder = 0;
        foreach ($groups as $title => $items) {
            $group = $plan->featureGroups()->create([
                'title' => $title,
                'sort_order' => $gOrder++,
            ]);

            $iOrder = 0;
            foreach ($items as $text) {
                $group->items()->create([
                    'feature_text' => $text,
                    'sort_order' => $iOrder++,
                ]);
            }
        }
    }
}
