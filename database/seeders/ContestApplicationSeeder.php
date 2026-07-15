<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Faker\Generator;

class ContestApplicationSeeder extends Seeder
{
    /**
     * IDs of users that have the Boss role.
     * Populated once at the start of run().
     */
    private array $bossUserIds = [];

    /**
     * Run the database seeds.
     *
     * Seeds contest_applications with 20-30 realistic records.
     * Only creates applications for businesses owned by Boss (business) role users.
     * Cleans up any existing applications for non-Boss users first.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Identify which users have the Boss role
        $this->bossUserIds = $this->getBossUserIds();

        // Clean up any existing applications for non-Boss businesses
        $this->cleanupNonBossApplications();

        /*
        |--------------------------------------------------------------------------
        | 1. Ensure there are enough seasons (completed, open, in_progress)
        |--------------------------------------------------------------------------
        */
        $seasons = $this->ensureSeasonsExist($faker);

        /*
        |--------------------------------------------------------------------------
        | 2. Ensure each season has rounds
        |--------------------------------------------------------------------------
        */
        $this->ensureRoundsExist($faker, $seasons);

        /*
        |--------------------------------------------------------------------------
        | 3. Ensure there are enough businesses owned by Boss users
        |--------------------------------------------------------------------------
        */
        $businesses = $this->ensureBusinessesExist($faker);

        /*
        |--------------------------------------------------------------------------
        | 4. Create 20-30 contest_applications for Boss-owned businesses
        |--------------------------------------------------------------------------
        */
        $this->createContestApplications($faker, $seasons, $businesses);

        /*
        |--------------------------------------------------------------------------
        | 5. Create Contestant records for approved applications in active rounds
        |--------------------------------------------------------------------------
        */
        $this->createContestantsFromApprovedApplications();
    }

    /**
     * Get user IDs that have the Boss (business owner) role.
     * Only these users are eligible for contest applications.
     */
    protected function getBossUserIds(): array
    {
        $rows = DB::select("
            SELECT DISTINCT u.id
            FROM users u
            JOIN model_has_roles mhr ON u.id = mhr.model_id AND mhr.model_type = ?
            JOIN roles r ON mhr.role_id = r.id
            WHERE r.name = 'boss'
        ", ['App\\Models\\User']);

        return array_map(fn ($row) => $row->id, $rows);
    }

    /**
     * Remove any existing contest applications for businesses NOT owned by Boss users.
     * This ensures a clean slate for generating Boss-only data.
     */
    protected function cleanupNonBossApplications(): void
    {
        if (empty($this->bossUserIds)) {
            return;
        }

        $ids = implode(',', array_map('intval', $this->bossUserIds));

        DB::statement("
            DELETE ca FROM contest_applications ca
            JOIN businesses b ON ca.business_id = b.id
            WHERE b.user_id NOT IN ({$ids})
        ");

        if (isset($this->command)) {
            $this->command->info('Cleaned up existing applications for non-Boss users.');
        }
    }

    /**
     * Create seasons with varied statuses if none (or too few) exist.
     */
    protected function ensureSeasonsExist(Generator $faker): array
    {
        $existingSeasons = DB::table('seasons')->get()->keyBy('id')->all();

        $neededSeasons = [];

        // Track which statuses we already have
        $existingStatuses = collect($existingSeasons)->pluck('status')->unique()->all();

        // Completed season
        if (! in_array('completed', $existingStatuses)) {
            $neededSeasons[] = [
                'contest_type'            => 'business',
                'title'                   => 'Business Innovation Challenge - Season 1',
                'slug'                    => 'business-innovation-challenge-season-1',
                'description'             => 'A premier competition for emerging businesses to showcase their innovation, impact, and growth potential. Open to startups and small businesses nationwide.',
                'status'                  => 'completed',
                'configuration'           => json_encode([
                    'max_contestants'  => 50,
                    'voting_strategy'  => 'popular_vote',
                    'scoring_rules'    => ['clap' => 1, 'save' => 2, 'share' => 3],
                ]),
                'applications_starts_at'  => '2026-01-01 00:00:00',
                'applications_ends_at'    => '2026-02-15 23:59:59',
                'starts_at'               => '2026-03-01 00:00:00',
                'ends_at'                 => '2026-05-31 23:59:59',
                'is_active'               => false,
                'is_featured'             => false,
                'metadata'                => json_encode(['winner_business_id' => null, 'total_applicants' => 24]),
                'created_at'              => '2025-12-15 10:00:00',
                'updated_at'              => '2026-05-31 23:59:59',
            ];
        }

        // Open season (accepting applications now)
        if (! in_array('open', $existingStatuses)) {
            $neededSeasons[] = [
                'contest_type'            => 'business',
                'title'                   => 'Local Business Spotlight - Season 2',
                'slug'                    => 'local-business-spotlight-season-2',
                'description'             => 'Highlighting the best local businesses making a difference in their communities. Apply now to showcase your story and compete for grants and exposure.',
                'status'                  => 'open',
                'configuration'           => json_encode([
                    'max_contestants'  => 100,
                    'voting_strategy'  => 'popular_vote',
                    'scoring_rules'    => ['clap' => 1, 'save' => 2, 'share' => 3],
                ]),
                'applications_starts_at'  => '2026-06-01 00:00:00',
                'applications_ends_at'    => '2026-08-15 23:59:59',
                'starts_at'               => '2026-09-01 00:00:00',
                'ends_at'                 => '2026-11-30 23:59:59',
                'is_active'               => true,
                'is_featured'             => true,
                'metadata'                => json_encode(['total_applicants' => 0]),
                'created_at'              => '2026-05-01 10:00:00',
                'updated_at'              => '2026-07-15 08:00:00',
            ];
        }

        // In-progress season (competition is running)
        if (! in_array('in_progress', $existingStatuses)) {
            $neededSeasons[] = [
                'contest_type'            => 'business',
                'title'                   => 'Startup Showdown - Season 4',
                'slug'                    => 'startup-showdown-season-4',
                'description'             => 'A fast-paced competition for early-stage startups to pitch their ideas, network with investors, and win seed funding. Applications are closed and the competition is now underway.',
                'status'                  => 'in_progress',
                'configuration'           => json_encode([
                    'max_contestants'  => 30,
                    'voting_strategy'  => 'judge_scored',
                    'scoring_rules'    => ['pitch' => 40, 'innovation' => 30, 'feasibility' => 30],
                ]),
                'applications_starts_at'  => '2026-04-01 00:00:00',
                'applications_ends_at'    => '2026-05-15 23:59:59',
                'starts_at'               => '2026-06-01 00:00:00',
                'ends_at'                 => '2026-08-31 23:59:59',
                'is_active'               => true,
                'is_featured'             => false,
                'metadata'                => json_encode(['total_applicants' => 28]),
                'created_at'              => '2026-03-01 10:00:00',
                'updated_at'              => '2026-06-01 08:00:00',
            ];
        }

        // Insert new seasons, checking slug uniqueness
        foreach ($neededSeasons as $data) {
            $existingId = DB::table('seasons')->where('slug', $data['slug'])->value('id');
            if (! $existingId) {
                DB::table('seasons')->insert($data);
            }
        }

        // Return all seasons suitable for applications
        return DB::table('seasons')
            ->whereIn('status', ['open', 'completed', 'in_progress'])
            ->orderBy('id')
            ->get()
            ->all();
    }

    /**
     * Create rounds for seasons that don't have them yet.
     */
    protected function ensureRoundsExist(Generator $faker, array $seasons): array
    {
        $allRounds = [];

        foreach ($seasons as $season) {
            $existingRounds = DB::table('rounds')
                ->where('season_id', $season->id)
                ->orderBy('round_number')
                ->get()
                ->all();

            if (count($existingRounds) > 0) {
                $allRounds = array_merge($allRounds, $existingRounds);
                continue;
            }

            // Create 3 rounds for each season
            $roundConfigs = [
                [
                    'title' => 'Preliminary Round',
                    'goal' => 'Submit your business pitch deck and a 2-minute introductory video showcasing your products or services.',
                    'requirements' => 'Upload a PDF pitch deck (max 20 slides) and a 2-minute intro video. Include revenue projections for the next 12 months.',
                    'voting_strategy' => 'popular_vote',
                    'advance_limit' => 20,
                    'video_duration' => 120,
                ],
                [
                    'title' => 'Semi-Finals',
                    'goal' => 'Present your community impact report and financial sustainability plan to the judging panel.',
                    'requirements' => 'Submit a community impact statement (500-1000 words) with supporting evidence. Include financial statements from the last 2 quarters.',
                    'voting_strategy' => 'popular_vote',
                    'advance_limit' => 10,
                    'video_duration' => 180,
                ],
                [
                    'title' => 'Grand Finals',
                    'goal' => 'Deliver a live pitch to the judging panel and answer Q&A about your business growth strategy.',
                    'requirements' => 'Prepare a 10-minute presentation covering business model, growth metrics, and community impact. Live Q&A session follows.',
                    'voting_strategy' => 'judge_scored',
                    'advance_limit' => 3,
                    'video_duration' => 600,
                ],
            ];

            for ($i = 0; $i < 3; $i++) {
                $config = $roundConfigs[$i];

                $roundData = [
                    'season_id'               => $season->id,
                    'round_number'            => $i + 1,
                    'title'                   => $config['title'],
                    'goal'                    => $config['goal'],
                    'requirements'            => $config['requirements'],
                    'voting_strategy'         => $config['voting_strategy'],
                    'submission_type'         => 'multi',
                    'submission_requirements' => json_encode([
                        'video'    => ['required' => true, 'max_duration_sec' => $config['video_duration']],
                        'document' => ['required' => true, 'formats' => ['pdf', 'docx']],
                    ]),
                    'advance_limit'           => $config['advance_limit'],
                    'elimination_rule'        => 'advance_limit',
                    'advancement_config'      => json_encode([
                        'top_n'       => $config['advance_limit'],
                        'tiebreakers' => ['total_points', 'community_votes'],
                    ]),
                    'is_active'               => $season->status === 'open' && $i === 0,
                    'starts_at'               => $season->status === 'open'
                                                    ? now()->addDays($i * 30)
                                                    : date('Y-m-d H:i:s', strtotime($season->starts_at . " +{$i} months")),
                    'ends_at'                 => $season->status === 'open'
                                                    ? now()->addDays($i * 30 + 28)
                                                    : date('Y-m-d H:i:s', strtotime($season->starts_at . " +{$i} months +28 days")),
                    'voting_ends_at'          => null,
                    'sort_order'              => $i + 1,
                    'metadata'                => null,
                    'created_at'              => $season->created_at ?? now(),
                    'updated_at'              => now(),
                ];

                DB::table('rounds')->insert($roundData);
            }

            $newRounds = DB::table('rounds')
                ->where('season_id', $season->id)
                ->orderBy('round_number')
                ->get()
                ->all();
            $allRounds = array_merge($allRounds, $newRounds);
        }

        return $allRounds;
    }

    /**
     * Create businesses owned by eligible (non-admin, non-artist) users.
     * Ensures we have enough businesses for 20-30 diverse applications.
     */
    protected function ensureBusinessesExist(Generator $faker): array
    {
        // Get existing businesses that are owned by Boss users
        $existingBusinesses = DB::table('businesses')
            ->whereNull('deleted_at')
            ->whereIn('user_id', $this->bossUserIds)
            ->get()
            ->all();

        // We need at least 10 Boss-owned businesses for sufficient application variety
        if (count($existingBusinesses) >= 10) {
            return $existingBusinesses;
        }

        // Get Boss users to assign as business owners
        $bossUsers = DB::table('users')
            ->whereIn('id', $this->bossUserIds)
            ->where('status', 'active')
            ->get()
            ->all();

        if (empty($bossUsers)) {
            if (isset($this->command)) {
                $this->command->warn('No Boss users found to assign as business owners.');
            }
            return $existingBusinesses;
        }

        $businessTemplates = [
            [
                'business_name' => 'Bloom & Root Organics',
                'owner_founder_name' => 'Sarah Mitchell',
                'story' => 'Started in a small backyard garden in 2021, Bloom & Root Organics has grown into a community-supported agriculture program serving over 200 families. We believe in chemical-free, locally-grown produce that nourishes both people and the planet.',
                'mission' => 'To make organic, locally-grown food accessible to every family in our community while supporting regenerative farming practices.',
                'website_social_media' => json_encode(['website' => 'https://bloomandroot.com', 'instagram' => 'https://instagram.com/bloomandroot']),
                'community_impact_statement' => 'We donate 10% of our weekly harvest to local food banks and run free gardening workshops for underserved youth.',
                'revenue_stage' => '50k-100k',
                'why_they_deserve_to_compete' => 'Bloom & Root represents the future of sustainable local food systems. Winning this competition would allow us to expand our CSA program and launch a mobile farmers market.',
                'status' => 'active',
                'is_featured' => true,
                'total_claps' => 342,
                'total_saves' => 89,
                'total_shares' => 156,
                'total_points' => 1287,
            ],
            [
                'business_name' => 'CodeCraft Academy',
                'owner_founder_name' => 'James Chen',
                'story' => 'CodeCraft Academy began as a weekend coding bootcamp in a co-working space. Three years later, we have trained over 500 students, with 85% job placement rates in tech roles.',
                'mission' => 'To democratize tech education and create pathways to high-paying tech careers for individuals from all backgrounds.',
                'website_social_media' => json_encode(['website' => 'https://codecraftacademy.io', 'linkedin' => 'https://linkedin.com/company/codecraft']),
                'community_impact_statement' => 'We have provided $200,000+ in scholarships to low-income students and partnered with 15 local schools for after-school coding clubs.',
                'revenue_stage' => '100k+',
                'why_they_deserve_to_compete' => 'CodeCraft Academy is proof that tech education can be both impactful and sustainable. We need funding to launch our free coding program for formerly incarcerated individuals.',
                'status' => 'active',
                'is_featured' => true,
                'total_claps' => 567,
                'total_saves' => 203,
                'total_shares' => 412,
                'total_points' => 2891,
            ],
            [
                'business_name' => 'Solara Energy Solutions',
                'owner_founder_name' => 'Amara Okafor',
                'story' => 'Solara Energy was founded after witnessing the impact of unreliable electricity in rural communities. We design and install affordable solar microgrids for off-grid households.',
                'mission' => 'To bring affordable, clean energy to every off-grid community, reducing carbon emissions through solar innovation.',
                'website_social_media' => json_encode(['website' => 'https://solarasolutions.com', 'twitter' => 'https://twitter.com/solaraenergy']),
                'community_impact_statement' => 'We have electrified 12 rural communities, installed 500+ solar home systems, and created 40 local jobs. Our systems offset an estimated 200 tons of CO2 annually.',
                'revenue_stage' => '50k-100k',
                'why_they_deserve_to_compete' => 'Solara Energy is at the forefront of the renewable energy transition in underserved areas. Competition funding would help us develop a pay-as-you-go financing model.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 234,
                'total_saves' => 67,
                'total_shares' => 98,
                'total_points' => 876,
            ],
            [
                'business_name' => 'Artisan & Spice',
                'owner_founder_name' => 'Priya Sharma',
                'story' => 'What started as a small stall at the local farmers market has become a beloved artisanal food brand. We source spices directly from small-scale farmers worldwide.',
                'mission' => 'To preserve culinary heritage and support small farmers by creating premium, ethically-sourced spice blends.',
                'website_social_media' => json_encode(['website' => 'https://artisanandspice.com', 'facebook' => 'https://facebook.com/artisanandspice']),
                'community_impact_statement' => 'We work directly with 50+ smallholder farmers in 8 countries, paying fair-trade premiums 20% above market rate.',
                'revenue_stage' => '10k-50k',
                'why_they_deserve_to_compete' => 'Artisan & Spice proves that businesses can be both profitable and principled. We need capital to build our e-commerce platform.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 178,
                'total_saves' => 45,
                'total_shares' => 67,
                'total_points' => 623,
            ],
            [
                'business_name' => 'Mindful Spaces Design',
                'owner_founder_name' => 'David Park',
                'story' => 'As a licensed architect and certified wellness designer, I founded Mindful Spaces to create environments that promote mental health through biophilic design.',
                'mission' => 'To transform built environments into spaces that actively support human health and well-being through evidence-based design.',
                'website_social_media' => json_encode(['website' => 'https://mindfulspaces.design', 'instagram' => 'https://instagram.com/mindfulspaces']),
                'community_impact_statement' => 'We have redesigned 8 community centers and 3 public schools, incorporating natural light and calming design. Post-occupancy studies show 30% improvement in well-being.',
                'revenue_stage' => '100k+',
                'why_they_deserve_to_compete' => 'Mental health is the defining public health challenge of our generation. Mindful Spaces is pioneering a new approach to architecture that puts well-being first.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 412,
                'total_saves' => 134,
                'total_shares' => 89,
                'total_points' => 1543,
            ],
            [
                'business_name' => 'ReThread Fashion',
                'owner_founder_name' => 'Maria Gonzalez',
                'story' => 'ReThread is a sustainable fashion brand creating trendy clothing exclusively from upcycled materials and deadstock fabric.',
                'mission' => 'To make sustainable fashion accessible and stylish while combating the environmental impact of fast fashion.',
                'website_social_media' => json_encode(['website' => 'https://rethreadfashion.com', 'tiktok' => 'https://tiktok.com/@rethreadfashion']),
                'community_impact_statement' => 'We have diverted 15,000+ pounds of textile waste from landfills, employed 12 local seamstresses, and taught free mending workshops to 300+ community members.',
                'revenue_stage' => '10k-50k',
                'why_they_deserve_to_compete' => 'Fashion is the second most polluting industry. ReThread proves style and sustainability can coexist. Funding would help launch our textile take-back program.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 298,
                'total_saves' => 156,
                'total_shares' => 234,
                'total_points' => 1245,
            ],
            [
                'business_name' => 'TechBridge Community Hub',
                'owner_founder_name' => 'Kwame Johnson',
                'story' => 'TechBridge started as a mobile computer lab in a converted van, bringing digital literacy training to underserved neighborhoods.',
                'mission' => 'To eliminate the digital divide by providing free or low-cost technology access, training, and support to underserved communities.',
                'website_social_media' => json_encode(['website' => 'https://techbridgehub.org', 'twitter' => 'https://twitter.com/techbridgehub']),
                'community_impact_statement' => 'We have trained 2,000+ individuals in digital skills, distributed 500+ refurbished computers, and provided free Wi-Fi to 5,000+ community members monthly.',
                'revenue_stage' => '50k-100k',
                'why_they_deserve_to_compete' => 'The digital divide is a barrier to opportunity. TechBridge is on the front lines closing that gap. Funding would help expand to three new locations.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 489,
                'total_saves' => 178,
                'total_shares' => 301,
                'total_points' => 1892,
            ],
            [
                'business_name' => 'PureCare Natural Products',
                'owner_founder_name' => 'Lisa Thompson',
                'story' => 'After struggling with sensitive skin, I started making natural soaps and skincare products in my kitchen. PureCare now produces a full line of vegan, cruelty-free products.',
                'mission' => 'To provide safe, effective, and affordable natural personal care products while promoting environmental stewardship.',
                'website_social_media' => json_encode(['website' => 'https://purecarenatural.com', 'facebook' => 'https://facebook.com/purecarenatural']),
                'community_impact_statement' => 'We source 80% of ingredients from local farms, use 100% compostable packaging, and donate 5% of profits to environmental conservation.',
                'revenue_stage' => '10k-50k',
                'why_they_deserve_to_compete' => 'Consumers deserve products that are good for them and the planet. PureCare is proving that natural personal care can scale sustainably.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 156,
                'total_saves' => 78,
                'total_shares' => 45,
                'total_points' => 567,
            ],
            [
                'business_name' => 'FreshBite Meal Prep',
                'owner_founder_name' => 'Carlos Mendez',
                'story' => 'FreshBite started as a meal prep service for busy professionals. We now serve 1,000+ weekly customers with healthy, chef-prepared meals using locally-sourced ingredients.',
                'mission' => 'To make healthy, convenient eating accessible to everyone while supporting local farmers and reducing food waste.',
                'website_social_media' => json_encode(['website' => 'https://freshbitemeals.com', 'instagram' => 'https://instagram.com/freshbitemeals']),
                'community_impact_statement' => 'We donate 500+ meals monthly to local shelters, partner with 12 local farms, and have reduced food waste by 40% through our predictive ordering system.',
                'revenue_stage' => '100k+',
                'why_they_deserve_to_compete' => 'FreshBite is tackling food insecurity and health inequality simultaneously. Competition funding would help us launch a subsidized meal program for low-income families.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 389,
                'total_saves' => 145,
                'total_shares' => 201,
                'total_points' => 1456,
            ],
            [
                'business_name' => 'GreenScape Urban Farms',
                'owner_founder_name' => 'Nia Williams',
                'story' => 'GreenScape transforms vacant urban lots into productive community farms. We grow fresh produce in food deserts, create green jobs, and beautify neighborhoods.',
                'mission' => 'To transform urban food deserts into thriving green spaces that provide fresh food, jobs, and community gathering places.',
                'website_social_media' => json_encode(['website' => 'https://greenscapeurban.com', 'twitter' => 'https://twitter.com/greenscape']),
                'community_impact_statement' => 'We have created 8 community farms on formerly vacant lots, produced 20,000+ pounds of fresh produce, and created 25 green jobs for local residents.',
                'revenue_stage' => '10k-50k',
                'why_they_deserve_to_compete' => 'GreenScape is proving that urban agriculture can be a viable business model while addressing food apartheid. Funding would help us replicate our model in 10 more neighborhoods.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 267,
                'total_saves' => 112,
                'total_shares' => 178,
                'total_points' => 923,
            ],
            [
                'business_name' => 'PetPals Veterinary Services',
                'owner_founder_name' => 'Dr. Rachel Kim',
                'story' => 'PetPals is a mobile veterinary clinic bringing affordable pet care to underserved communities. We believe that financial circumstances should never prevent pets from receiving care.',
                'mission' => 'To make quality veterinary care accessible and affordable for all pet owners, regardless of income or location.',
                'website_social_media' => json_encode(['website' => 'https://petpalsvet.com', 'facebook' => 'https://facebook.com/petpalsvet']),
                'community_impact_statement' => 'We have provided 3,000+ low-cost vaccinations, 500+ free spay/neuter surgeries, and partnered with 8 animal shelters to reduce pet overpopulation.',
                'revenue_stage' => '50k-100k',
                'why_they_deserve_to_compete' => 'PetPals fills a critical gap in veterinary access. Competition support would help us purchase a second mobile clinic van and expand to neighboring counties.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 345,
                'total_saves' => 98,
                'total_shares' => 156,
                'total_points' => 1102,
            ],
            [
                'business_name' => 'BrewLab Coffee Co.',
                'owner_founder_name' => 'Marcus Thompson',
                'story' => 'BrewLab started as a pop-up coffee cart at farmers markets. Today we roast our own single-origin beans and operate three community-focused coffee shops.',
                'mission' => 'To create community gathering spaces over exceptional coffee while ensuring farmers receive fair compensation for their craft.',
                'website_social_media' => json_encode(['website' => 'https://brewlabcoffee.com', 'instagram' => 'https://instagram.com/brewlabcoffee']),
                'community_impact_statement' => 'We source directly from 6 coffee cooperatives in 4 countries, pay 25% above Fair Trade prices, and host monthly community events at each of our locations.',
                'revenue_stage' => '100k+',
                'why_they_deserve_to_compete' => 'BrewLab is building a business model that prioritizes people and planet alongside profit. Funding would help us launch our barista training program for opportunity youth.',
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => 423,
                'total_saves' => 167,
                'total_shares' => 289,
                'total_points' => 1678,
            ],
        ];

        // Count current Boss-owned businesses
        $currentCount = count($existingBusinesses);
        $userIndex = 0;
        $bossUserCount = count($bossUsers);

        foreach ($businessTemplates as $template) {
            if (count(DB::table('businesses')->whereNull('deleted_at')
                ->whereIn('user_id', $this->bossUserIds)->get()) >= 10) {
                break;
            }

            $slug = Str::slug($template['business_name']) . '-' . uniqid();

            // Assign a rotating Boss user as owner
            $ownerId = $bossUsers[$userIndex % $bossUserCount]->id;
            $userIndex++;

            // Skip if slug already exists
            if (DB::table('businesses')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('businesses')->insert(array_merge($template, [
                'user_id' => $ownerId,
                'owner_name' => $template['owner_founder_name'],
                'slug' => $slug,
                'created_at' => now()->subDays(rand(30, 90)),
                'updated_at' => now()->subDays(rand(1, 10)),
            ]));

            $currentCount++;
        }

        // Return only businesses owned by Boss users
        return DB::table('businesses')
            ->whereNull('deleted_at')
            ->whereIn('user_id', $this->bossUserIds)
            ->get()
            ->all();
    }

    /**
     * Create 20-30 contest_applications with realistic data.
     * Only creates applications for businesses owned by eligible (non-admin, non-artist) users.
     */
    protected function createContestApplications(Generator $faker, array $seasons, array $businesses): void
    {
        // Find an admin user for approved_by reference
        $adminIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'admin')
            ->select('users.id')
            ->pluck('id')
            ->all();

        $adminId = ! empty($adminIds) && DB::table('users')->where('id', $adminIds[0])->exists()
            ? $adminIds[0]
            : null;

        // Filter businesses to only Boss-owned ones
        $bossBusinesses = array_values(array_filter($businesses, function ($b) {
            return in_array($b->user_id, $this->bossUserIds);
        }));

        if (empty($bossBusinesses)) {
            if (isset($this->command)) {
                $this->command->warn('No Boss-owned businesses found for contest applications.');
            }
            return;
        }

        // Load existing (business_id, season_id) pairs from DB for idempotency
        $usedPairs = DB::table('contest_applications')
            ->get(['business_id', 'season_id'])
            ->map(fn ($row) => $row->business_id . '-' . $row->season_id)
            ->flip()
            ->all();

        $targetTotal = rand(20, 30);
        $createdCount = 0;
        $maxAttempts = 100; // Safety valve to prevent infinite loops
        $attempts = 0;

        while ($createdCount < $targetTotal && $attempts < $maxAttempts) {
            $attempts++;

            // Pick a random Boss-owned business and season
            $business = $faker->randomElement($bossBusinesses);
            $season = $faker->randomElement($seasons);

            $pairKey = $business->id . '-' . $season->id;
            if (isset($usedPairs[$pairKey])) {
                continue; // Skip if this pair already exists
            }
            $usedPairs[$pairKey] = true;

            // Determine application status based on season status
            $status = $this->determineApplicationStatus($season->status, $faker);

            // Build the application record
            $application = [
                'business_id'    => $business->id,
                'season_id'      => $season->id,
                'status'         => $status,
                'metadata'       => $this->generateApplicationMetadata($faker, $status),
                'created_at'     => $this->generateApplicationDate($season, $status),
                'updated_at'     => now(),
            ];

            // Add AI review fields for processed statuses
            if (in_array($status, ['approved', 'rejected', 'needs_review'])) {
                $aiVerdict = match ($status) {
                    'approved'     => 'approve',
                    'rejected'     => 'reject',
                    'needs_review' => 'needs_review',
                    default        => null,
                };
                $aiConfidence = match ($status) {
                    'approved'     => round(0.85 + mt_rand(0, 14) / 100, 2),
                    'rejected'     => round(0.75 + mt_rand(0, 20) / 100, 2),
                    'needs_review' => round(0.50 + mt_rand(0, 30) / 100, 2),
                    default        => null,
                };

                $application['ai_reviewed_at'] = date('Y-m-d H:i:s', strtotime($application['created_at'] . ' +' . rand(1, 48) . ' hours'));
                $application['ai_verdict'] = $aiVerdict;
                $application['ai_confidence'] = $aiConfidence;
            }

            // Add admin fields for approved applications
            if ($status === 'approved') {
                $application['approved_at'] = date('Y-m-d H:i:s', strtotime($application['created_at'] . ' +' . rand(2, 72) . ' hours'));
                $application['approved_by'] = $adminId;
                $application['admin_note'] = $faker->randomElement([
                    'Strong application with clear community impact. Welcome to the competition!',
                    'Excellent business model and growth potential. Approved for participation.',
                    'Application meets all criteria. Impressive sustainability practices.',
                    'Great pitch and solid financial projections. Glad to have you on board.',
                    'Outstanding community involvement and clear revenue model. Approved.',
                    'Your business demonstrates strong potential for growth and impact. Welcome aboard!',
                ]);
            } elseif ($status === 'rejected') {
                $application['rejected_reason'] = $faker->randomElement([
                    'Business does not meet the minimum revenue requirements for this season.',
                    'Incomplete application. Missing required documentation for community impact verification.',
                    'The proposed business model does not align with this season\'s focus on sustainability.',
                    'Application submitted after the deadline. Please reapply for the next season.',
                    'Business category does not match the current competition track.',
                    'Required documentation for financial verification was not provided.',
                ]);
            }

            DB::table('contest_applications')->insert($application);
            $createdCount++;
        }

        $totalApplications = DB::table('contest_applications')->count();

        if (isset($this->command)) {
            $this->command->info("Created {$createdCount} new contest application(s). Total in database: {$totalApplications}.");
        }
    }

    /**
     * Determine a realistic application status based on the season's overall status.
     */
    protected function determineApplicationStatus(string $seasonStatus, Generator $faker): string
    {
        return match ($seasonStatus) {
            'completed' => $faker->randomElement([
                'approved', 'approved', 'approved',
                'rejected', 'rejected',
            ]),
            'open' => $faker->randomElement([
                'pending', 'pending', 'pending', 'pending',
                'ai_review', 'ai_review',
                'needs_review', 'needs_review',
                'approved', 'approved',
                'rejected',
            ]),
            'in_progress' => $faker->randomElement([
                'approved', 'approved', 'approved', 'approved',
                'rejected',
            ]),
            default => 'pending',
        };
    }

    /**
     * Generate a realistic application creation date based on season timeline.
     */
    protected function generateApplicationDate(object $season, string $status): string
    {
        if ($season->status === 'completed' && $season->applications_starts_at) {
            $start = strtotime($season->applications_starts_at);
            $end = strtotime($season->applications_ends_at ?? $season->ends_at);
            if ($start === false || $end === false) {
                return now()->subDays(rand(1, 45))->format('Y-m-d H:i:s');
            }
            $randomTimestamp = rand($start, min($end, time()));
            return date('Y-m-d H:i:s', $randomTimestamp);
        }

        if ($season->status === 'open') {
            return now()->subDays(rand(1, 30))->format('Y-m-d H:i:s');
        }

        if ($season->status === 'in_progress') {
            $start = $season->applications_starts_at
                ? strtotime($season->applications_starts_at)
                : strtotime('-90 days');
            $end = $season->applications_ends_at
                ? strtotime($season->applications_ends_at)
                : strtotime('-30 days');

            if ($start === false || $end === false) {
                return now()->subDays(rand(1, 45))->format('Y-m-d H:i:s');
            }

            $randomTimestamp = rand($start, min($end, time()));
            return date('Y-m-d H:i:s', $randomTimestamp);
        }

        return now()->subDays(rand(1, 45))->format('Y-m-d H:i:s');
    }

    /**
     * Create Contestant records for all approved contest applications.
     *
     * A business must have an active Contestant record linked to the current round
     * before they can submit a round submission. This method creates those records
     * for any approved application in a season that has an active round.
     */
    protected function createContestantsFromApprovedApplications(): void
    {
        $approvedApps = DB::table('contest_applications')
            ->where('status', 'approved')
            ->get();

        if ($approvedApps->isEmpty()) {
            if (isset($this->command)) {
                $this->command->info('No approved applications found to create contestants.');
            }
            return;
        }

        // Fetch active rounds keyed by season_id
        $activeRounds = DB::table('rounds')
            ->where('is_active', true)
            ->get()
            ->keyBy('season_id');

        // Fetch business names
        $businessIds = $approvedApps->pluck('business_id')->unique()->all();
        $businesses = DB::table('businesses')
            ->whereNull('deleted_at')
            ->whereIn('id', $businessIds)
            ->get()
            ->keyBy('id');

        // Load existing contestants to avoid unique constraint violations
        $existingPairs = DB::table('contestants')
            ->where('contestable_type', 'App\\\\Models\\\\Business')
            ->get(['contestable_id', 'season_id'])
            ->map(fn ($row) => $row->contestable_id . '-' . $row->season_id)
            ->flip()
            ->all();

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($approvedApps as $app) {
            $pairKey = $app->business_id . '-' . $app->season_id;

            if (isset($existingPairs[$pairKey])) {
                $skippedCount++;
                continue;
            }

            $business = $businesses[$app->business_id] ?? null;
            if (! $business) {
                $skippedCount++;
                continue;
            }

            $activeRound = $activeRounds[$app->season_id] ?? null;
            if (! $activeRound) {
                // No active round for this season — still create contestant but without current_round_id
            }

            DB::table('contestants')->insert([
                'season_id'          => $app->season_id,
                'contestable_type'   => 'App\\Models\\Business',
                'contestable_id'     => $app->business_id,
                'display_name'       => $business->business_name,
                'slug'               => Str::slug($business->business_name) . '-' . uniqid(),
                'status'             => 'active',
                'current_round_id'   => $activeRound?->id,
                'entered_at'         => $app->approved_at ?? $app->created_at ?? now(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // Mark the pair as used
            $existingPairs[$pairKey] = true;
            $createdCount++;
        }

        if (isset($this->command)) {
            $total = DB::table('contestants')->count();
            $this->command->info("Created {$createdCount} contestant(s) from approved applications ({$skippedCount} skipped). Total contestants: {$total}.");
        }
    }

    /**
     * Generate metadata for the application.
     */
    protected function generateApplicationMetadata(Generator $faker, string $status): ?string
    {
        if (rand(1, 100) > 40) {
            return null;
        }

        $metadata = [
            'application_source' => $faker->randomElement(['website', 'mobile_app', 'referral', 'social_media']),
            'previous_attempts'  => $faker->randomElement([0, 0, 0, 1, 2]),
            'has_pitch_deck'     => $faker->boolean(80),
        ];

        if ($status === 'approved') {
            $metadata['review_notes'] = $faker->randomElement([
                'Strong community focus. Recommended for fast-track.',
                'Excellent financial projections. Consider for spotlight.',
                'Verified community impact statements. Approve.',
            ]);
        }

        return json_encode($metadata);
    }
}
