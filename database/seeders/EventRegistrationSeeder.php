<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicketTier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure we have at least one active event and ticket tier
        $event = Event::where('status', 'published')->first();
        if (!$event) {
            $event = Event::factory()->create(['status' => 'published']);
        }

        $ticketTier = EventTicketTier::firstOrCreate(
            ['event_id' => $event->id, 'name' => 'General Admission'],
            [
                'price' => 0, // Free for easy testing
                'capacity' => 100,
                'is_active' => true,
            ]
        );

        // 2. Get one user of each role type based on emails from UserSeeder
        $boss = User::where('email', 'like', 'boss%')
            ->offset(1)
            ->first();
        $user = User::where('email', 'like', 'user%')->first();
        $artist = User::where('email', 'like', 'artist%')->first();

        // If they don't exist, just grab any user or create one
        if (!$boss)
            $boss = User::firstOrCreate(['email' => 'boss_seeder@test.com'], ['password' => bcrypt('password'), 'status' => 'active']);
        if (!$user)
            $user = User::firstOrCreate(['email' => 'user_seeder@test.com'], ['password' => bcrypt('password'), 'status' => 'active']);
        if (!$artist)
            $artist = User::firstOrCreate(['email' => 'artist_seeder@test.com'], ['password' => bcrypt('password'), 'status' => 'active']);

        $accountsToSeed = [
            'Boss' => $boss,
            'Artist' => $artist,
            'User' => $user
        ];

        foreach ($accountsToSeed as $roleName => $account) {
            if (!$account) {
                $this->command->warn("No account found for role: {$roleName}. Skipping registration for this role.");
                continue;
            }

            // Create a "confirmed" registration
            EventRegistration::create([
                'booking_reference' => EventRegistration::generateBookingReference(),
                'status' => 'confirmed',
                'event_id' => $event->id,
                'ticket_tier_id' => $ticketTier->id,
                'user_id' => $account->id,
                'first_name' => 'Test',
                'last_name' => $roleName,
                'email' => $account->email,
                'phone_number' => $account->phone ?? '1234567890',
                'quantity' => 1,
                'unit_price' => $ticketTier->price,
                'service_fee' => 0,
                'subtotal' => $ticketTier->price,
                'total' => $ticketTier->price,
                'currency' => 'USD',
                'payment_status' => 'paid',
                'paid_at' => now(),
                'confirmed_at' => now(),
            ]);

            // Create a "pending" registration (useful for testing cancel API)
            EventRegistration::create([
                'booking_reference' => EventRegistration::generateBookingReference(),
                'status' => 'pending',
                'event_id' => $event->id,
                'ticket_tier_id' => $ticketTier->id,
                'user_id' => $account->id,
                'first_name' => 'Pending',
                'last_name' => $roleName,
                'email' => $account->email,
                'phone_number' => $account->phone ?? '1234567890',
                'quantity' => 2,
                'unit_price' => $ticketTier->price,
                'service_fee' => 0,
                'subtotal' => $ticketTier->price * 2,
                'total' => $ticketTier->price * 2,
                'currency' => 'USD',
                'payment_status' => 'pending',
            ]);

            $this->command->info("Seeded 2 registrations (1 Confirmed, 1 Pending) for {$roleName} (Email: {$account->email})");
        }

        $this->command->info('Event Registration seeding completed successfully!');
    }
}
