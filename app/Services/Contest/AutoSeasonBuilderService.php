<?php

namespace App\Services\Contest;

use App\Models\Contest\Season;
use App\Models\Round;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoSeasonBuilderService
{
    /**
     * Round duration definitions in days:
     * Round 1: 2 Weeks (14 Days)
     * Round 2: 3 Weeks (21 Days)
     * Round 3: 2 Weeks (14 Days)
     * Round 4: 2 Weeks (14 Days)
     * Round 5: 1 Week (7 Days)
     * Total: 70 Days (~10 Weeks / 3 Months)
     */
    protected const ROUND_CONFIGURATIONS = [
        [
            'round_number' => 1,
            'title' => 'Preliminary Round',
            'duration_days' => 14,
            'goal' => 'Submit your business pitch deck and a 2-minute introductory video showcasing your products or services.',
            'requirements' => 'Upload a PDF pitch deck (max 20 slides) and a 2-minute intro video. Include revenue projections for the next 12 months and a brief overview of your target market.',
            'voting_strategy' => 'popular_vote',
            'submission_type' => 'multi',
            'advance_limit' => 15,
            'elimination_rule' => 'advance_limit',
            'video_duration_sec' => 120,
        ],
        [
            'round_number' => 2,
            'title' => 'Qualifiers',
            'duration_days' => 21,
            'goal' => 'Present your community impact report and financial sustainability plan to the judging panel.',
            'requirements' => 'Submit a community impact statement (500-1000 words) with supporting evidence. Include financial statements from the last 2 quarters and customer testimonials.',
            'voting_strategy' => 'popular_vote',
            'submission_type' => 'multi',
            'advance_limit' => 14,
            'elimination_rule' => 'advance_limit',
            'video_duration_sec' => 180,
        ],
        [
            'round_number' => 3,
            'title' => 'Semi-Finals',
            'duration_days' => 14,
            'goal' => 'Demonstrate your business growth strategy and competitive advantage in a live presentation.',
            'requirements' => 'Prepare a 5-minute presentation covering business model, growth metrics, and competitive analysis. Submit a detailed growth roadmap for the next 12 months.',
            'voting_strategy' => 'popular_vote',
            'submission_type' => 'multi',
            'advance_limit' => 12,
            'elimination_rule' => 'advance_limit',
            'video_duration_sec' => 300,
        ],
        [
            'round_number' => 4,
            'title' => 'Finals Preparation',
            'duration_days' => 14,
            'goal' => 'Refine your business pitch and participate in a mock judging session with business mentors.',
            'requirements' => 'Submit a refined pitch deck incorporating feedback from previous rounds. Record a 3-minute pitch video. Complete a mentorship feedback form.',
            'voting_strategy' => 'popular_vote',
            'submission_type' => 'multi',
            'advance_limit' => 10,
            'elimination_rule' => 'advance_limit',
            'video_duration_sec' => 180,
        ],
        [
            'round_number' => 5,
            'title' => 'Grand Finals',
            'duration_days' => 7,
            'goal' => 'Deliver your final live pitch to the grand judging panel and answer Q&A about your business vision.',
            'requirements' => 'Prepare a 10-minute live presentation covering business model, growth metrics, community impact, and future vision. Live Q&A session follows with judges and audience.',
            'voting_strategy' => 'judge_scored',
            'submission_type' => 'multi',
            'advance_limit' => 3,
            'elimination_rule' => 'top_percent',
            'video_duration_sec' => 600,
        ],
    ];

    /**
     * Ensure an upcoming Boss Beginnings season is scheduled.
     * Creates a new 3-month season + 5 rounds if no upcoming season exists.
     *
     * @return Season|null
     */
    public function ensureUpcomingSeasonExists(): ?Season
    {
        // Check if an upcoming season (starting after now) already exists
        $upcomingExists = Season::where('contest_type', 'business')
            ->where('starts_at', '>', now())
            ->exists();

        if ($upcomingExists) {
            return null;
        }

        return DB::transaction(function () {
            // Find the latest existing season to derive the new start date
            $latestSeason = Season::where('contest_type', 'business')
                ->orderByDesc('ends_at')
                ->orderByDesc('id')
                ->first();

            $now = Carbon::now();
            $adminStartDate = Setting::current()?->boss_beginnings_start_date;

            if ($latestSeason && $latestSeason->ends_at && $latestSeason->ends_at->isAfter($now)) {
                $seasonStartsAt = $latestSeason->ends_at->copy();
            } elseif ($adminStartDate && $adminStartDate->isAfter($now)) {
                $seasonStartsAt = $adminStartDate->copy();
            } else {
                $seasonStartsAt = $now->copy();
            }

            // Calculate total season duration across rounds
            $totalDays = array_sum(array_column(self::ROUND_CONFIGURATIONS, 'duration_days'));
            $seasonEndsAt = $seasonStartsAt->copy()->addDays($totalDays);

            // Application window: opens 14 days before season start (or immediately if starting now)
            $applicationsStartsAt = $seasonStartsAt->copy()->subDays(14);
            if ($applicationsStartsAt->isBefore($now)) {
                $applicationsStartsAt = $now->copy();
            }
            $applicationsEndsAt = $seasonStartsAt->copy();

            // Generate title and slug
            $seasonCount = Season::where('contest_type', 'business')->count() + 1;
            $title = "Boss Beginnings - Season {$seasonCount}";
            $slug = Str::slug($title) . '-' . $seasonStartsAt->format('Y-m-d');

            // Status decision: if application window has started and no other season is active/open, status can be open
            $hasActiveSeason = Season::where('contest_type', 'business')
                ->where('is_active', true)
                ->exists();

            $status = (!$hasActiveSeason && $applicationsStartsAt->lte($now) && $applicationsEndsAt->gt($now))
                ? 'open'
                : 'draft';

            $isActive = ($status === 'open');

            $season = Season::create([
                'contest_type' => 'business',
                'title' => $title,
                'slug' => $slug,
                'description' => 'Automatic 3-month competition cycle for emerging businesses featuring 5 structured rounds of pitch decks, community impact, growth strategies, and grand finals.',
                'status' => $status,
                'configuration' => [
                    'max_contestants' => 50,
                    'voting_strategy' => 'popular_vote',
                    'scoring_rules' => ['clap' => 1, 'save' => 2, 'share' => 3],
                    'auto_created' => true,
                ],
                'applications_starts_at' => $applicationsStartsAt,
                'applications_ends_at' => $applicationsEndsAt,
                'starts_at' => $seasonStartsAt,
                'ends_at' => $seasonEndsAt,
                'is_active' => $isActive,
                'is_featured' => true,
                'metadata' => [
                    'auto_generated' => true,
                    'created_at_timestamp' => $now->toIso8601String(),
                ],
            ]);

            // Create the 5 Rounds with calculated timelines
            $currentRoundStart = $seasonStartsAt->copy();

            foreach (self::ROUND_CONFIGURATIONS as $config) {
                $roundEnd = $currentRoundStart->copy()->addDays($config['duration_days']);
                $votingEnd = $roundEnd->copy();

                Round::create([
                    'season_id' => $season->id,
                    'round_number' => $config['round_number'],
                    'title' => $config['title'],
                    'goal' => $config['goal'],
                    'requirements' => $config['requirements'],
                    'voting_strategy' => $config['voting_strategy'],
                    'submission_type' => $config['submission_type'],
                    'submission_requirements' => [
                        'video' => ['required' => true, 'max_duration_sec' => $config['video_duration_sec']],
                        'document' => ['required' => true, 'formats' => ['pdf', 'docx', 'pptx']],
                    ],
                    'advance_limit' => $config['advance_limit'],
                    'elimination_rule' => $config['elimination_rule'],
                    'advancement_config' => [
                        'top_n' => $config['advance_limit'],
                        'categories' => ['innovation', 'presentation', 'impact'],
                        'max_score_per_category' => 10,
                        'tiebreakers' => ['total_points', 'community_votes', 'judge_score'],
                    ],
                    'is_active' => false,
                    'starts_at' => $currentRoundStart,
                    'ends_at' => $roundEnd,
                    'voting_ends_at' => $votingEnd,
                    'sort_order' => $config['round_number'],
                ]);

                // Next round starts when current round ends
                $currentRoundStart = $roundEnd->copy();
            }

            Log::info("AutoSeasonBuilderService: Created upcoming season #{$season->id} ({$season->title}) from {$seasonStartsAt} to {$seasonEndsAt}");

            return $season;
        });
    }
}
