<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicketTier;
use App\Models\Profile;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Checkout\Session;

class EventRegistrationService
{
    public function __construct(
        protected StripeService $stripeService
    ) {}

    /**
     * Initiate a new event registration.
     */
    public function initiate(array $data, ?int $userId = null): array
    {
        $event = Event::findOrFail($data['event_id']);

        $tier = EventTicketTier::where('id', $data['ticket_tier_id'])
            ->where('event_id', $event->id)
            ->firstOrFail();

        // Validate availability
        if ($tier->quantity_available !== null) {
            $remaining = $tier->quantity_available - $tier->quantity_sold;
            if ($remaining < $data['quantity']) {
                throw new RuntimeException('Not enough tickets available in this tier.');
            }
        }

        $isGuest = $userId === null;
        $user = null;
        $tokenJwt = null;

        return DB::transaction(function () use ($data, $event, $tier, $isGuest, $userId, &$user, &$tokenJwt) {

            // 1. Handle Guest Auto-Registration
            if ($isGuest) {
                // Check if email already exists
                $existingUser = User::where('email', $data['email'])->first();
                if ($existingUser) {
                    throw new RuntimeException('Email already registered. Please login to purchase tickets.');
                }

                // Create Member User (Role 6)
                $user = User::create([
                    'email' => strtolower($data['email']),
                    'password' => Hash::make($data['password']),
                    'status' => 'active', // Automatically active since payment will verify intent
                    'email_verified_at' => now(), // Skip OTP verify
                ]);

                $name = $data['first_name'] . ' ' . $data['last_name'];
                Profile::create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'username' => Helper::generateUsername($name),
                    'slug' => Helper::generateSlug($name),
                ]);

                DB::table('model_has_roles')->insert([
                    'role_id' => 6, // Member role
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ]);

                $userId = $user->id;

                // Login new user to generate JWT
                $tokenJwt = auth('api')->login($user);
            } else {
                $user = User::find($userId);
            }

            // 2. Calculate Pricing
            $unitPrice = $tier->price;
            $quantity = $data['quantity'];
            $subtotal = $unitPrice * $quantity;
            $serviceFeePercent = 0.05;
            $serviceFee = $subtotal * $serviceFeePercent;
            $total = $subtotal + $serviceFee;

            // 3. Create Registration
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'ticket_tier_id' => $tier->id,
                'user_id' => $userId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'service_fee' => $serviceFee,
                'subtotal' => $subtotal,
                'total' => $total,
                'currency' => 'USD',
                'payment_status' => 'pending',
                'status' => 'pending', // Waiting for payment
            ]);

            // 4. Handle Free vs Paid Tickets
            if ($total <= 0) {
                // Confirm immediately
                $registration->update([
                    'status' => 'confirmed',
                    'payment_status' => 'free',
                    'confirmed_at' => now(),
                    'paid_at' => now(),
                ]);

                $tier->increment('quantity_sold', $quantity);

                return [
                    'checkout_url' => null,
                    'registration' => $registration,
                    'token' => $tokenJwt,
                    'message' => 'Free registration confirmed.',
                ];
            }

            // 5. Create Stripe Checkout Session
            try {
                $checkoutSession = $this->stripeService->createCheckoutSession([
                    'order_id' => $registration->id,
                    'order_number' => $registration->booking_reference, // Using this as ref
                    'amount' => (float) $total,
                    'customer_email' => $user->email ?? $data['email'],
                    'line_items' => [
                        [
                            'name' => $event->title . ' - ' . $tier->name . ' Ticket',
                            'quantity' => $quantity,
                            'price' => $total / $quantity, // Pass total per item including fees
                        ]
                    ],
                    'metadata' => [
                        'type' => 'event_registration',
                        'registration_id' => (string) $registration->id,
                        'booking_reference' => $registration->booking_reference,
                    ],
                ]);

                $registration->update([
                    'stripe_checkout_session_id' => $checkoutSession->id,
                ]);

                return [
                    'checkout_url' => $checkoutSession->url,
                    'session_id' => $checkoutSession->id,
                    'registration' => $registration,
                    'token' => $tokenJwt,
                    'message' => 'Redirecting to payment...',
                ];
            } catch (Exception $e) {
                Log::error('Stripe Checkout Error for Event Registration: ' . $e->getMessage());
                throw new RuntimeException('Failed to initiate payment.');
            }
        });
    }

    /**
     * Mark registration as paid (called by webhook).
     */
    public function markAsPaid(int $registrationId, ?string $sessionId, ?string $paymentIntentId): EventRegistration
    {
        $registration = EventRegistration::findOrFail($registrationId);

        if ($registration->payment_status === 'paid') {
            return $registration; // Already paid
        }

        DB::transaction(function () use ($registration, $sessionId, $paymentIntentId) {
            $registration->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'stripe_checkout_session_id' => $sessionId ?? $registration->stripe_checkout_session_id,
                'stripe_payment_intent_id' => $paymentIntentId,
                'paid_at' => now(),
                'confirmed_at' => now(),
            ]);

            // Increment quantity sold
            if ($registration->ticketTier) {
                $registration->ticketTier->increment('quantity_sold', $registration->quantity);
            }
        });

        return $registration->fresh(['event', 'ticketTier']);
    }

    /**
     * Cancel a pending registration (e.g. session expired).
     */
    public function cancel(int $registrationId, string $reason = 'Cancelled'): EventRegistration
    {
        $registration = EventRegistration::findOrFail($registrationId);

        if (in_array($registration->status, ['confirmed', 'cancelled', 'attended'])) {
            return $registration;
        }

        $registration->update([
            'status' => 'cancelled',
            'payment_status' => 'failed',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $registration;
    }
}
