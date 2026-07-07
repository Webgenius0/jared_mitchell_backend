<?php

namespace Database\Factories;

use App\Models\Round;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoundFactory extends Factory
{
    protected $model = Round::class;

    public function definition(): array
    {
        return [
            'round_number'      => 1,
            'round_session_id'  => 1,
            'title'             => fake()->words(3, true),
            'sort_order'        => 1,
            'is_active'         => true,
            'elimination_rule'  => 'advance_limit',
            'advancement_config'=> ['advance_limit' => 3],
            'voting_strategy'   => 'popular_vote',
            'submission_type'   => 'multi',
            'starts_at'         => now()->subDay(),
            'ends_at'           => now()->addDay(),
        ];
    }
}
