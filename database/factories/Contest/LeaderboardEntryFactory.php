<?php

namespace Database\Factories\Contest;

use App\Models\Contest\LeaderboardEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaderboardEntryFactory extends Factory
{
    protected $model = LeaderboardEntry::class;

    public function definition(): array
    {
        return [
            'rank'         => 1,
            'total_score'  => 0,
            'votes_count'  => 0,
            'avg_score'    => null,
            'snapshot'     => [
                'display_name'       => fake()->company(),
                'avatar_url'         => null,
                'contestant_slug'    => fake()->slug(),
                'contestable_name'   => fake()->company(),
                'contestable_avatar' => null,
            ],
            'calculated_at' => now(),
        ];
    }
}
