<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Faker\Generator;

class RoundSessionSeeder extends Seeder
{
    /**
     * IDs of users that have the Boss (business) role.
     */
    private array $bossUserIds = [];

    /**
     * Run the seeder.
     *
     * Creates:
     * 1. A Season active for ~2 months with 5 rounds
     * 2. 15+ businesses with media (images/video) in business_media table
     * 3. Approved contest applications for those businesses
     * 4. Contestants assigned to each round (10+ per round)
     * 5. Round submissions for contestants
     */
    public function run(): void
    {
        $faker = Faker::create();

        $this->bossUserIds = $this->getBossUserIds();

        if (empty($this->bossUserIds)) {
            $this->command?->warn('No Boss users found. Creating default Boss users...');
            $this->ensureBossUsersExist();
            $this->bossUserIds = $this->getBossUserIds();

            if (empty($this->bossUserIds)) {
                $this->command?->error('Failed to create Boss users. Aborting.');
                return;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Find an admin user for approved_by reference
        |--------------------------------------------------------------------------
        */
        $adminId = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'admin')
            ->select('users.id')
            ->value('users.id');

        /*
        |--------------------------------------------------------------------------
        | 2. Create a Sponsor if none exist (for the season)
        |--------------------------------------------------------------------------
        */
        $sponsorId = DB::table('sponsors')->where('is_active', true)->value('id');
        if (!$sponsorId) {
            $sponsorId = DB::table('sponsors')->insertGetId([
                'name' => 'Boss Beginnings Foundation',
                'description' => 'Official sponsor of the Boss Beginnings Summer Championship',
                'website_url' => 'https://bossbeginnings.com',
                'is_active' => true,
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Create the Season (Round Session) — active for ~2 months
        |--------------------------------------------------------------------------
        */
        $seasonTitle = 'Boss Beginnings - Summer Championship 2026';
        $seasonSlug = 'boss-beginnings-summer-championship-2026';

        // Check if season already exists
        $existingSeason = DB::table('seasons')->where('slug', $seasonSlug)->first();
        if ($existingSeason) {
            $this->command?->warn("Season '{$seasonTitle}' already exists (ID: {$existingSeason->id}). Skipping creation.");
            $season = $existingSeason;
        } else {
            // Dates: started 1 week ago, ends ~7 weeks from now = ~2 months total
            $startsAt = now()->subWeek();
            $endsAt = now()->addWeeks(7);

            $seasonId = DB::table('seasons')->insertGetId([
                'contest_type' => 'business',
                'title' => $seasonTitle,
                'slug' => $seasonSlug,
                'description' => 'The premier summer competition for emerging businesses to showcase innovation, community impact, and growth potential. Featuring 5 rounds of intense competition with expert judging and community voting.',
                'status' => 'in_progress',
                'configuration' => json_encode([
                    'max_contestants' => 50,
                    'voting_strategy' => 'popular_vote',
                    'scoring_rules' => ['clap' => 1, 'save' => 2, 'share' => 3],
                ]),
                'applications_starts_at' => $startsAt->copy()->subMonths(2),
                'applications_ends_at' => $startsAt->copy()->subDays(2),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'is_active' => true,
                'is_featured' => true,
                'metadata' => json_encode([
                    'total_applicants' => 18,
                    'winner_business_id' => null,
                ]),
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
            ]);

            $season = DB::table('seasons')->find($seasonId);

            // Link sponsor
            if (!DB::table('season_sponsor')->where('season_id', $seasonId)->exists()) {
                DB::table('season_sponsor')->insert([
                    'season_id' => $seasonId,
                    'sponsor_id' => $sponsorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command?->info("Created Season: {$seasonTitle} (ID: {$seasonId}) — active ~2 months");
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Create 5 Rounds within the season — each ~12 days
        |--------------------------------------------------------------------------
        */
        $seasonStarts = \Carbon\Carbon::parse($season->starts_at);
        $totalDays = 60; // ~2 months

        $roundConfigs = [
            [
                'title' => 'Preliminary Round',
                'goal' => 'Submit your business pitch deck and a 2-minute introductory video showcasing your products or services.',
                'requirements' => 'Upload a PDF pitch deck (max 20 slides) and a 2-minute intro video. Include revenue projections for the next 12 months and a brief overview of your target market.',
                'advance_limit' => 15,
                'video_duration' => 120,
            ],
            [
                'title' => 'Qualifiers',
                'goal' => 'Present your community impact report and financial sustainability plan to the judging panel.',
                'requirements' => 'Submit a community impact statement (500-1000 words) with supporting evidence. Include financial statements from the last 2 quarters and customer testimonials.',
                'advance_limit' => 14,
                'video_duration' => 180,
            ],
            [
                'title' => 'Semi-Finals',
                'goal' => 'Demonstrate your business growth strategy and competitive advantage in a live presentation.',
                'requirements' => 'Prepare a 5-minute presentation covering business model, growth metrics, and competitive analysis. Submit a detailed growth roadmap for the next 12 months.',
                'advance_limit' => 12,
                'video_duration' => 300,
            ],
            [
                'title' => 'Finals Preparation',
                'goal' => 'Refine your business pitch and participate in a mock judging session with business mentors.',
                'requirements' => 'Submit a refined pitch deck incorporating feedback from previous rounds. Record a 3-minute pitch video. Complete a mentorship feedback form.',
                'advance_limit' => 10,
                'video_duration' => 180,
            ],
            [
                'title' => 'Grand Finals',
                'goal' => 'Deliver your final live pitch to the grand judging panel and answer Q&A about your business vision.',
                'requirements' => 'Prepare a 10-minute live presentation covering business model, growth metrics, community impact, and future vision. Live Q&A session follows with judges and audience.',
                'advance_limit' => 3,
                'video_duration' => 600,
            ],
        ];

        $existingRounds = DB::table('rounds')
            ->where('season_id', $season->id)
            ->orderBy('round_number')
            ->get()
            ->all();

        if (count($existingRounds) >= 5) {
            $this->command?->warn('Rounds already exist for this season. Skipping round creation.');
            $rounds = $existingRounds;
        } else {
            // Delete any partial rounds first
            DB::table('rounds')->where('season_id', $season->id)->delete();

            $rounds = [];
            $daysPerRound = $totalDays / count($roundConfigs);

            foreach ($roundConfigs as $i => $config) {
                $roundStart = $seasonStarts->copy()->addDays((int) round($i * $daysPerRound));
                $roundEnd = $seasonStarts->copy()->addDays((int) round(($i + 1) * $daysPerRound) - 1);
                $votingEnd = $roundEnd->copy()->addDays(2); // voting extends slightly

                $roundId = DB::table('rounds')->insertGetId([
                    'season_id' => $season->id,
                    'round_number' => $i + 1,
                    'title' => $config['title'],
                    'goal' => $config['goal'],
                    'requirements' => $config['requirements'],
                    'voting_strategy' => $i === 4 ? 'judge_scored' : 'popular_vote',
                    'submission_type' => 'multi',
                    'submission_requirements' => json_encode([
                        'video' => ['required' => true, 'max_duration_sec' => $config['video_duration']],
                        'document' => ['required' => true, 'formats' => ['pdf', 'docx', 'pptx']],
                    ]),
                    'advance_limit' => $config['advance_limit'],
                    'elimination_rule' => $i === 4 ? 'top_percent' : 'advance_limit',
                    'advancement_config' => json_encode([
                        'top_n' => $config['advance_limit'],
                        'tiebreakers' => ['total_points', 'community_votes', 'judge_score'],
                    ]),
                    'is_active' => $i === 0, // Only first round active initially
                    'starts_at' => $roundStart,
                    'ends_at' => $roundEnd,
                    'voting_ends_at' => $votingEnd,
                    'sort_order' => $i + 1,
                    'metadata' => null,
                    'created_at' => now()->subMonths(2),
                    'updated_at' => now(),
                ]);

                $rounds[] = DB::table('rounds')->find($roundId);
                $this->command?->info("  Created Round {$i}: {$config['title']} (days " . ($i * 12 + 1) . '-' . (($i + 1) * 12) . ')');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Create 18 unique businesses with realistic data
        |--------------------------------------------------------------------------
        */
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
            ],
            [
                'business_name' => 'NextGen Robotics',
                'owner_founder_name' => 'Alex Rivera',
                'story' => 'NextGen Robotics designs affordable STEM education kits for K-12 schools. Our mission is to inspire the next generation of engineers and innovators through hands-on learning.',
                'mission' => 'To make robotics and STEM education accessible to every student regardless of their school\'s budget or location.',
                'website_social_media' => json_encode(['website' => 'https://nextgenrobotics.com', 'linkedin' => 'https://linkedin.com/company/nextgen-robotics']),
                'community_impact_statement' => 'We have provided robotics kits to 200+ underserved schools, trained 500+ teachers in STEM curriculum, and sponsored 30 robotics competitions in low-income districts.',
                'revenue_stage' => '50k-100k',
                'why_they_deserve_to_compete' => 'NextGen Robotics is shaping the future workforce. Competition funding would help us develop a free online curriculum platform for remote learning communities.',
            ],
            [
                'business_name' => 'Harbor Wellness Studio',
                'owner_founder_name' => 'Jasmine Taylor',
                'story' => 'Harbor Wellness is a sliding-scale yoga and meditation studio that makes mental wellness accessible to all income levels. We believe self-care should not be a luxury.',
                'mission' => 'To create a sanctuary for mental wellness where everyone, regardless of income, can access the healing benefits of yoga, meditation, and community support.',
                'website_social_media' => json_encode(['website' => 'https://harborwellness.com', 'instagram' => 'https://instagram.com/harborwellness']),
                'community_impact_statement' => 'We have provided 5,000+ free classes to low-income community members, trained 20 community wellness ambassadors, and partnered with 10 local health clinics.',
                'revenue_stage' => '10k-50k',
                'why_they_deserve_to_compete' => 'Harbor Wellness is proving that mental health services can be both accessible and sustainable. Funding would help us open a second location in a neighboring food desert.',
            ],
            [
                'business_name' => 'Urban Roots Landscaping',
                'owner_founder_name' => 'Darnell Washington',
                'story' => 'Urban Roots provides eco-friendly landscaping services while employing and training formerly incarcerated individuals, giving them a second chance at meaningful careers.',
                'mission' => 'To beautify urban spaces while providing dignified employment and skill-building opportunities for individuals reentering society.',
                'website_social_media' => json_encode(['website' => 'https://urbanrootslandscaping.com', 'facebook' => 'https://facebook.com/urbanroots']),
                'community_impact_statement' => 'We have employed 25+ formerly incarcerated individuals, maintained 50+ community green spaces, and reduced recidivism among our employees to under 5%.',
                'revenue_stage' => '50k-100k',
                'why_they_deserve_to_compete' => 'Urban Roots proves that businesses can drive social justice while being profitable. Funding would help us launch a paid apprenticeship program for returning citizens.',
            ],
            [
                'business_name' => 'SpiceRoute Catering',
                'owner_founder_name' => 'Fatima Hassan',
                'story' => 'SpiceRoute Catering celebrates the rich culinary traditions of the African diaspora. We specialize in fusion cuisine that tells stories of heritage, resilience, and community.',
                'mission' => 'To preserve and celebrate African diaspora culinary heritage through exceptional catering while creating economic opportunities for immigrant and refugee chefs.',
                'website_social_media' => json_encode(['website' => 'https://spiceroutecatering.com', 'instagram' => 'https://instagram.com/spiceroutecatering']),
                'community_impact_statement' => 'We employ 15 immigrant and refugee chefs, source ingredients from minority-owned farms, and provide free community meals at cultural festivals.',
                'revenue_stage' => '10k-50k',
                'why_they_deserve_to_compete' => 'SpiceRoute is building bridges through food while creating economic empowerment for new Americans. Funding would help us launch a food truck program.',
            ],
            [
                'business_name' => 'EcoGuard Home Solutions',
                'owner_founder_name' => 'Tyler Brooks',
                'story' => 'EcoGuard provides energy-efficient home retrofitting services for low-income households, helping families save money on utilities while reducing their carbon footprint.',
                'mission' => 'To make energy-efficient home improvements accessible to low-income households, reducing both energy bills and environmental impact.',
                'website_social_media' => json_encode(['website' => 'https://ecoguardhomes.com', 'twitter' => 'https://twitter.com/ecoguard']),
                'community_impact_statement' => 'We have retrofitted 300+ low-income homes, reducing energy bills by an average of 35%, and trained 20 local workers in green construction skills.',
                'revenue_stage' => '50k-100k',
                'why_they_deserve_to_compete' => 'EcoGuard proves that sustainability and social equity go hand in hand. Funding would help us scale our retrofit program to serve 1,000+ additional households.',
            ],
            [
                'business_name' => 'BrightPath Learning Center',
                'owner_founder_name' => 'Dr. Keisha Brown',
                'story' => 'BrightPath is an after-school STEAM program serving at-risk youth in underserved neighborhoods. We provide tutoring, mentorship, and hands-on projects in science and technology.',
                'mission' => 'To close the opportunity gap by providing high-quality STEAM education and mentorship to at-risk youth in underserved communities.',
                'website_social_media' => json_encode(['website' => 'https://brightpathlearning.org', 'facebook' => 'https://facebook.com/brightpathlearning']),
                'community_impact_statement' => 'We serve 400+ students annually across 5 locations, with 95% high school graduation rate among participants and 80% pursuing higher education.',
                'revenue_stage' => '10k-50k',
                'why_they_deserve_to_compete' => 'BrightPath is transforming lives through education. Competition funding would help us open two additional locations and launch our summer STEAM camp program.',
            ],
        ];

        $businesses = $this->createBusinesses($businessTemplates, $season);

        /*
        |--------------------------------------------------------------------------
        | 6. Create business_media (images + videos) for each business
        |--------------------------------------------------------------------------
        */
        $this->createBusinessMedia($businesses, $faker);

        /*
        |--------------------------------------------------------------------------
        | 7. Create approved Contest Applications for each business
        |--------------------------------------------------------------------------
        */
        $this->createContestApplications($businesses, $season, $adminId, $faker);

        /*
        |--------------------------------------------------------------------------
        | 8. Create Contestants assigned to each round (10+ per round)
        |--------------------------------------------------------------------------
        */
        $contestantsByRound = $this->createContestants($businesses, $season, $rounds, $faker);

        /*
        |--------------------------------------------------------------------------
        | 9. Create Round Submissions for each contestant
        |--------------------------------------------------------------------------
        */
        $this->createRoundSubmissions($contestantsByRound, collect($rounds)->keyBy('round_number')->all(), $faker);

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */
        if ($this->command) {
            $totalBusinesses = DB::table('businesses')->whereNull('deleted_at')->count();
            $totalMedia = DB::table('business_media')->count();
            $totalApplications = DB::table('contest_applications')->count();
            $totalContestants = DB::table('contestants')->count();
            $totalSubmissions = DB::table('round_submissions')->count();

            $this->command->info('');
            $this->command->info('=== Round Session Seeder Summary ===');
            $this->command->info("Season: {$season->title}");
            $this->command->info("Duration: {$season->starts_at} to {$season->ends_at}");
            $this->command->info("Rounds: " . count($rounds));
            $this->command->info("Businesses: {$totalBusinesses}");
            $this->command->info("Business Media: {$totalMedia}");
            $this->command->info("Contest Applications: {$totalApplications}");
            $this->command->info("Contestants: {$totalContestants}");
            $this->command->info("Round Submissions: {$totalSubmissions}");
            $this->command->info('===================================');
        }
    }

    /**
     * Ensure at least 10 Boss users exist for business assignment.
     */
    protected function ensureBossUsersExist(): void
    {
        $existing = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'boss')
            ->count();

        if ($existing >= 10) {
            return;
        }

        // Ensure the boss role exists
        $bossRole = DB::table('roles')->where('name', 'boss')->first();
        if (!$bossRole) {
            $bossRoleId = DB::table('roles')->insertGetId([
                'name' => 'boss',
                'guard_name' => 'api',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $bossRoleId = $bossRole->id;
        }

        $createdCount = 0;

        for ($i = 1; $i <= 10; $i++) {
            $email = "boss_seeder_{$i}@gmail.com";

            $existingUser = DB::table('users')->where('email', $email)->first();
            if ($existingUser) {
                continue;
            }

            $userId = DB::table('users')->insertGetId([
                'email' => $email,
                'phone' => '0191000' . str_pad((string) (80 + $i), 4, '0', STR_PAD_LEFT),
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
            ]);

            DB::table('model_has_roles')->insert([
                'role_id' => $bossRoleId,
                'model_type' => 'App\Models\User',
                'model_id' => $userId,
            ]);

            $createdCount++;
        }

        $this->command?->info("Created {$createdCount} Boss users for the round session.");
    }

    /**
     * Get user IDs that have the Boss role.
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

        return array_map(fn($row) => $row->id, $rows);
    }

    /**
     * Create businesses and return them as an array of objects.
     */
    protected function createBusinesses(array $templates, object $season): array
    {
        $bossUsers = DB::table('users')
            ->whereIn('id', $this->bossUserIds)
            ->where('status', 'active')
            ->get()
            ->all();

        if (empty($bossUsers)) {
            $this->command?->warn('No active Boss users found.');
            return [];
        }

        $existingBusinesses = DB::table('businesses')
            ->whereNull('deleted_at')
            ->whereIn('user_id', $this->bossUserIds)
            ->get()
            ->keyBy('business_name')
            ->all();

        $createdBusinesses = [];
        $userIndex = 0;
        $bossUserCount = count($bossUsers);

        foreach ($templates as $template) {
            $businessName = $template['business_name'];
            $slug = Str::slug($businessName);

            // Check if business already exists by name
            if (isset($existingBusinesses[$businessName])) {
                $createdBusinesses[] = $existingBusinesses[$businessName];
                continue;
            }

            // Check by slug
            $existing = DB::table('businesses')->where('slug', $slug)->first();
            if ($existing) {
                $createdBusinesses[] = $existing;
                continue;
            }

            $ownerId = $bossUsers[$userIndex % $bossUserCount]->id;
            $userIndex++;

            $businessId = DB::table('businesses')->insertGetId(array_merge($template, [
                'user_id' => $ownerId,
                'slug' => $slug,
                'status' => 'active',
                'is_featured' => false,
                'total_claps' => rand(50, 600),
                'total_saves' => rand(20, 200),
                'total_shares' => rand(30, 300),
                'total_points' => rand(200, 2000),
                'owner_name' => $template['owner_founder_name'],
                'photo_video' => 'businesses/photos/business_photo_' . rand(1, 12) . '.jpg',
                'created_at' => $season->applications_starts_at
                    ? date('Y-m-d H:i:s', strtotime($season->applications_starts_at . ' +' . rand(1, 20) . ' days'))
                    : now()->subMonths(rand(1, 2)),
                'updated_at' => now()->subDays(rand(1, 7)),
            ]));

            $createdBusinesses[] = DB::table('businesses')->find($businessId);
        }

        $this->command?->info('Created/Found ' . count($createdBusinesses) . ' businesses for the round session.');

        return $createdBusinesses;
    }

    /**
     * Create media records (images and videos) for each business.
     * Stores file paths in the business_media table.
     */
    protected function createBusinessMedia(array $businesses, Generator $faker): void
    {
        $imageFiles = [
            'businesses/photos/business_photo_01.jpg',
            'businesses/photos/business_photo_02.jpg',
            'businesses/photos/business_photo_03.jpg',
            'businesses/photos/business_photo_04.jpg',
            'businesses/photos/business_photo_05.jpg',
            'businesses/photos/business_photo_06.jpg',
            'businesses/photos/business_photo_07.jpg',
            'businesses/photos/business_photo_08.jpg',
            'businesses/photos/business_photo_09.jpg',
            'businesses/photos/business_photo_10.jpg',
            'businesses/photos/business_photo_11.jpg',
            'businesses/photos/business_photo_12.jpg',
        ];

        $videoFiles = [
            'businesses/videos/pitch_video_01.mp4',
            'businesses/videos/pitch_video_02.mp4',
            'businesses/videos/pitch_video_03.mp4',
            'businesses/videos/pitch_video_04.mp4',
            'businesses/videos/pitch_video_05.mp4',
            'businesses/videos/pitch_video_06.mp4',
        ];

        $createdCount = 0;

        foreach ($businesses as $business) {
            // Skip if media already exists for this business
            $existingMediaCount = DB::table('business_media')
                ->where('business_id', $business->id)
                ->count();

            if ($existingMediaCount > 0) {
                continue;
            }

            // Each business gets 2-4 images
            $numImages = rand(2, 4);
            $usedImageIndices = [];

            for ($i = 0; $i < $numImages; $i++) {
                $imgIdx = array_rand($imageFiles);
                while (in_array($imgIdx, $usedImageIndices)) {
                    $imgIdx = array_rand($imageFiles);
                }
                $usedImageIndices[] = $imgIdx;

                DB::table('business_media')->insert([
                    'business_id' => $business->id,
                    'file_path' => $imageFiles[$imgIdx],
                    'file_name' => 'business_photo_' . ($imgIdx + 1) . '.jpg',
                    'mime_type' => 'image/jpeg',
                    'file_size' => rand(50000, 500000), // 50KB - 500KB
                    'created_at' => now()->subMonths(rand(1, 3)),
                    'updated_at' => now(),
                ]);
                $createdCount++;
            }

            // 50% of businesses also get a video
            if (rand(0, 1) === 1) {
                $videoIdx = array_rand($videoFiles);

                DB::table('business_media')->insert([
                    'business_id' => $business->id,
                    'file_path' => $videoFiles[$videoIdx],
                    'file_name' => 'pitch_video_' . ($videoIdx + 1) . '.mp4',
                    'mime_type' => 'video/mp4',
                    'file_size' => rand(2000000, 15000000), // 2MB - 15MB
                    'created_at' => now()->subMonths(rand(1, 3)),
                    'updated_at' => now(),
                ]);
                $createdCount++;
            }
        }

        $this->command?->info("Created {$createdCount} business media records (images & videos).");
    }

    /**
     * Create approved contest applications for all businesses.
     */
    protected function createContestApplications(array $businesses, object $season, ?int $adminId, Generator $faker): void
    {
        $existingPairs = DB::table('contest_applications')
            ->where('season_id', $season->id)
            ->get(['business_id'])
            ->pluck('business_id')
            ->flip()
            ->all();

        $createdCount = 0;

        foreach ($businesses as $business) {
            if (isset($existingPairs[$business->id])) {
                continue;
            }

            $createdAt = $season->applications_starts_at
                ? date('Y-m-d H:i:s', strtotime($season->applications_starts_at . ' +' . rand(1, 30) . ' days'))
                : now()->subDays(rand(10, 40));

            $approvedAt = date('Y-m-d H:i:s', strtotime($createdAt . ' +' . rand(1, 72) . ' hours'));

            $aiConfidence = round(0.80 + mt_rand(0, 19) / 100, 2);

            DB::table('contest_applications')->insert([
                'business_id' => $business->id,
                'season_id' => $season->id,
                'status' => 'approved',
                'ai_reviewed_at' => $createdAt,
                'ai_verdict' => 'approve',
                'ai_confidence' => $aiConfidence,
                'approved_at' => $approvedAt,
                'approved_by' => $adminId,
                'admin_note' => $faker->randomElement([
                    'Strong application with clear community impact. Welcome to the competition!',
                    'Excellent business model and growth potential. Approved for participation.',
                    'Application meets all criteria. Impressive sustainability practices.',
                    'Great pitch and solid financial projections. Glad to have you on board.',
                    'Outstanding community involvement and clear revenue model. Approved.',
                ]),
                'metadata' => json_encode([
                    'application_source' => $faker->randomElement(['website', 'mobile_app', 'referral', 'social_media']),
                    'has_pitch_deck' => true,
                ]),
                'created_at' => $createdAt,
                'updated_at' => now(),
            ]);

            $createdCount++;
        }

        $this->command?->info("Created {$createdCount} approved contest applications.");
    }

    /**
     * Create contestants and assign them to rounds.
     *
     * Distribution:
     * - Round 1: 15 contestants
     * - Round 2: 14 contestants (all from R1)
     * - Round 3: 12 contestants (all from R2)
     * - Round 4: 10 contestants (all from R3)
     * - Round 5:  3 contestants (top from R4)
     *
     * Returns array of [round_number => [contestant, ...]]
     */
    protected function createContestants(array $businesses, object $season, array $rounds, Generator $faker): array
    {
        $rounds = collect($rounds)->keyBy('round_number')->all();

        // If contestants already exist for this season, skip entirely to avoid corrupting data.
        $existingCount = DB::table('contestants')->where('season_id', $season->id)->count();
        if ($existingCount > 0) {
            $this->command?->info('Contestants already exist for this season. Skipping contestant creation.');
            return [];
        }

        $existingContestants = []; // No existing contestants on first run

        $perRound = [
            1 => 15,
            2 => 14,
            3 => 12,
            4 => 10,
            5 => 3,
        ];

        $contestantsByRound = [];
        $createdCount = 0;
        $skippedCount = 0;

        // Assign which businesses are in which round
        $businessIds = collect($businesses)->pluck('id')->all();

        // Round 1: pick 15 businesses
        $round1BusinessIds = array_slice($businessIds, 0, 15);

        // Round 2: pick 14 from round 1 (drop 1)
        $round2BusinessIds = array_slice($round1BusinessIds, 0, 14);

        // Round 3: pick 12 from round 2 (drop 2)
        $round3BusinessIds = array_slice($round2BusinessIds, 0, 12);

        // Round 4: pick 10 from round 3 (drop 2)
        $round4BusinessIds = array_slice($round3BusinessIds, 0, 10);

        // Round 5: pick 3 from round 4 (drop 7)
        $round5BusinessIds = array_slice($round4BusinessIds, 0, 3);

        $roundAssignments = [
            1 => $round1BusinessIds,
            2 => $round2BusinessIds,
            3 => $round3BusinessIds,
            4 => $round4BusinessIds,
            5 => $round5BusinessIds,
        ];

        foreach ($roundAssignments as $roundNumber => $assignedBusinessIds) {
            $round = $rounds[$roundNumber] ?? null;
            if (!$round) {
                continue;
            }

            $roundContestants = [];

            foreach ($assignedBusinessIds as $bid) {
                $business = collect($businesses)->firstWhere('id', $bid);
                if (!$business) {
                    continue;
                }

                // Check if already a contestant in this season
                if (isset($existingContestants[$bid])) {
                    // Do NOT update current_round_id — keep all contestants at Round 1
                    // so the Round 1 leaderboard correctly shows active participants.
                    // (Each contestant can only have one current_round_id, so they
                    // display in whichever round matches. Round 1 is where they start.)
                    $roundContestants[] = $existingContestants[$bid];
                    $skippedCount++;
                    continue;
                }

                $contestantId = DB::table('contestants')->insertGetId([
                    'season_id' => $season->id,
                    'contestable_type' => 'App\Models\Business',
                    'contestable_id' => $business->id,
                    'display_name' => $business->business_name,
                    'slug' => Str::slug($business->business_name) . '-' . uniqid(),
                    'avatar_url' => null,
                    'status' => 'active',
                    'total_score' => round(rand(50, 950) / 10, 2), // 5.0 - 95.0
                    'current_round_id' => $round->id,
                    'eliminated_in_round_id' => null,
                    'entered_at' => $season->starts_at,
                    'eliminated_at' => null,
                    'metadata' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $contestant = DB::table('contestants')->find($contestantId);
                $roundContestants[] = $contestant;
                $existingContestants[$bid] = $contestant;
                $createdCount++;
            }

            $contestantsByRound[$roundNumber] = $roundContestants;
        }

        // Eliminate the 1 contestant who doesn't advance from Round 1 to Round 2.
        // All other contestants stay at current_round_id = round1.id with status = 'active'.
        // This makes the Round 1 leaderboard show 14 active + 1 eliminated.
        $eliminatedFromR1 = array_diff($round1BusinessIds, $round2BusinessIds);
        foreach ($eliminatedFromR1 as $bid) {
            if (isset($existingContestants[$bid])) {
                DB::table('contestants')
                    ->where('id', $existingContestants[$bid]->id)
                    ->update([
                        'status' => 'eliminated',
                        'eliminated_in_round_id' => $rounds[1]->id,
                        'eliminated_at' => $rounds[1]->ends_at,
                    ]);
            }
        }
        // Note: Rounds 2-5 eliminations are skipped because all contestants share
        // the same current_round_id (Round 1). Eliminating for later rounds would
        // remove contestants that the Round 1 leaderboard should show as active.

        $this->command?->info("Created {$createdCount} new contestants ({$skippedCount} existing) with elimination assignments.");
        $this->command?->info('Round distribution:');
        foreach ($contestantsByRound as $rn => $cs) {
            $this->command?->info("  Round {$rn}: " . count($cs) . " contestants");
        }

        return $contestantsByRound;
    }

    /**
     * Create round submissions for each contestant.
     */
    protected function createRoundSubmissions(array $contestantsByRound, array $roundsByNumber, Generator $faker): void
    {
        $submissionTitles = [
            'Business Pitch Deck & Overview',
            'Community Impact Report',
            'Growth Strategy Presentation',
            'Financial Sustainability Plan',
            'Innovation Showcase',
            'Customer Success Stories',
            'Market Analysis Report',
            'Brand Identity & Marketing Plan',
            'Operations & Scaling Strategy',
            'Sustainability & ESG Report',
        ];

        $createdCount = 0;

        foreach ($contestantsByRound as $roundNumber => $contestants) {
            $round = $roundsByNumber[$roundNumber] ?? null;
            if (!$round) {
                continue;
            }
            $roundId = $round->id;

            foreach ($contestants as $contestant) {
                // Check if submission already exists for this contestant + round
                $existingSubmission = DB::table('round_submissions')
                    ->where('contestant_id', $contestant->id)
                    ->where('round_id', $roundId)
                    ->first();

                if ($existingSubmission) {
                    continue;
                }

                $mediaFiles = [];
                // Add 1-3 media files to the submission
                $numMedia = rand(1, 3);
                for ($i = 0; $i < $numMedia; $i++) {
                    $mediaFiles[] = 'submissions/' . $contestant->id . '/' . $faker->uuid() . '.' . $faker->randomElement(['pdf', 'mp4', 'jpg', 'pptx']);
                }

                $title = $submissionTitles[array_rand($submissionTitles)];

                DB::table('round_submissions')->insert([
                    'contestant_id' => $contestant->id,
                    'round_id' => $roundId,
                    'title' => $title,
                    'description' => $faker->randomElement([
                        'Complete submission for this round including all required materials.',
                        'Please find our detailed submission covering all requirements.',
                        'Submission prepared with our mentor\'s guidance. Includes all requested materials.',
                        'Thank you for the opportunity to present our progress this round.',
                    ]),
                    'media_urls' => json_encode($mediaFiles),
                    'status' => 'submitted',
                    'score' => round(rand(500, 950) / 10, 2), // 50.0 - 95.0
                    'submitted_at' => now()->subDays(rand(1, 14)),
                    'metadata' => json_encode([
                        'file_count' => count($mediaFiles),
                        'submission_method' => 'online',
                    ]),
                    'created_at' => now()->subDays(rand(15, 30)),
                    'updated_at' => now()->subDays(rand(1, 14)),
                ]);

                $createdCount++;
            }
        }

        $this->command?->info("Created {$createdCount} round submissions.");
    }
}
