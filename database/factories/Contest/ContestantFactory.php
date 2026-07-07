<?php

namespace Database\Factories\Contest;

use App\Models\Contest\Contestant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContestantFactory extends Factory
{
    protected $model = Contestant::class;

    public function definition(): array
    {
        $uniqueId = fake()->unique()->randomNumber(6);
        return [
            'contestable_type'  => 'App\\Models\\User',
            'contestable_id'    => $uniqueId,
            'display_name'      => fake()->company(),
            'slug'              => Str::slug(fake()->unique()->company()),
            'status'            => 'active',
            'total_score'       => 0,
            'entered_at'        => now(),
            'metadata'          => [],
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attrs) => ['status' => 'active']);
    }

    public function eliminated(): static
    {
        return $this->state(fn(array $attrs) => [
            'status'                => 'eliminated',
            'eliminated_at'         => now(),
        ]);
    }
}
