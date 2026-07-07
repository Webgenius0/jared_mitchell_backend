<?php

namespace Database\Factories\Contest;

use App\Models\Contest\Season;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        $title = fake()->unique()->company() . ' Season';

        return [
            'contest_type'            => 'business',
            'title'                   => $title,
            'slug'                    => Str::slug($title),
            'description'             => fake()->paragraph(),
            'status'                  => 'open',
            'configuration'           => [
                'max_contestants'  => 100,
                'voting_strategy'  => 'popular_vote',
                'scoring_rules'    => ['clap' => 1, 'save' => 2, 'share' => 3],
            ],
            'applications_starts_at'  => now()->subDays(30),
            'applications_ends_at'    => now()->addDays(30),
            'starts_at'               => now()->subDays(30),
            'ends_at'                 => now()->addDays(90),
            'is_active'               => true,
            'is_featured'             => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attrs) => [
            'is_active' => false,
            'status'    => 'closed',
        ]);
    }
}
