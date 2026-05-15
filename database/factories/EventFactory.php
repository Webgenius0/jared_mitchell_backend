<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        $startsAt = $this->faker->dateTimeBetween('+1 week', '+1 month');
        $endsAt = clone $startsAt;
        $endsAt->modify('+2 hours');

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph(5),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'timezone' => 'UTC',
            'venue_name' => $this->faker->company . ' Hall',
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'hosted_by' => $this->faker->name,
            'cover_image_path' => 'https://placehold.co/800x450?text=Event+Cover',
            'promo_video_path' => null,
            'event_type' => $this->faker->randomElement(['featured', 'workshop', 'art_exhibition', 'pop_up', 'networking', 'other']),
            'is_spotlight_eligible' => $this->faker->boolean(30),
            'is_featured' => $this->faker->boolean(20),
            'like_count' => $this->faker->numberBetween(0, 500),
            'ticket_url' => $this->faker->url,
            'tickets_available' => true,
            'status' => $this->faker->randomElement(['draft', 'published', 'cancelled', 'completed']),
            'created_by' => User::role('admin')->first()?->id ?? User::factory(),
        ];
    }
}
