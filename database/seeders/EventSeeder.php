<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventTicketTier;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::factory()->count(10)->create()->each(function ($event) {
            // Create a few ticket tiers for each event
            EventTicketTier::create([
                'event_id' => $event->id,
                'name' => 'General Admission',
                'description' => 'Standard entry to the event.',
                'price' => 25.00,
                'quantity_available' => 100,
                'quantity_sold' => 0,
                'is_active' => true,
                'sort_order' => 0,
            ]);

            EventTicketTier::create([
                'event_id' => $event->id,
                'name' => 'VIP Access',
                'description' => 'Includes backstage pass and drinks.',
                'price' => 75.00,
                'quantity_available' => 20,
                'quantity_sold' => 0,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        });
    }
}
