<?php

namespace Database\Factories\Contest;

use App\Models\Contest\RoundTransition;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoundTransitionFactory extends Factory
{
    protected $model = RoundTransition::class;

    public function definition(): array
    {
        return [
            'status'             => 'completed',
            'elimination_rule'   => 'advance_limit',
            'transition_config'  => ['advance_limit' => 5],
            'total_contestants'  => 10,
            'advanced_count'     => 5,
            'eliminated_count'   => 5,
            'advanced_contestants'   => [],
            'eliminated_contestants' => [],
            'processed_at'       => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attrs) => ['status' => 'pending']);
    }

    public function failed(): static
    {
        return $this->state(fn(array $attrs) => [
            'status'   => 'failed',
            'metadata' => ['error' => 'Test failure'],
        ]);
    }
}
