<?php

namespace Database\Factories;

use App\Models\RoundSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoundSessionFactory extends Factory
{
    protected $model = RoundSession::class;

    public function definition(): array
    {
        return [
            'title'      => fake()->words(3, true),
            'slug'       => Str::slug(fake()->unique()->words(3, true)),
            'is_active'  => true,
            'starts_at'  => now()->subDays(10),
            'ends_at'    => now()->addDays(10),
        ];
    }
}
