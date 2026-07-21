<?php

namespace App\Services\Spotlight;

use App\Mail\SpotlightVotePurchaseRequestAdminNotification;
use App\Models\Spotlight\SpotlightVotePackage;
use App\Models\Spotlight\SpotlightVotePurchase;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Models\User;
use App\Services\StripeService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class SpotlightVotePurchaseService
{
    public function __construct(
        protected StripeService $stripeService,
    ) {}

    /**
     * Request a vote purchase (pending admin approval).
     *
     * Creates a new pending purchase record and notifies the admin via email.
     *
     * @return array{success: bool, message: string, purchase?: SpotlightVotePurchase}
     */
    public function requestPurchase(
        User $user,
        SpotlightWeekNominee $nominee,
        SpotlightVotePackage $package
    ): array {
        // 1. Validate voting is open
        $nominee->load('week');
        if (! $nominee->week->isVotingOpen()) {
            return [
                'success' => false,
                'message' => 'Voting is not currently open. You cannot purchase votes.',
            ];
        }

        // 2. Check cap: total pending + completed votes must not exceed 100
        $existingTotal = SpotlightVotePurchase::where('spotlight_week_nominee_id', $nominee->id)
            ->whereIn('status', [SpotlightVotePurchase::STATUS_PENDING, SpotlightVotePurchase::STATUS_APPROVED, SpotlightVotePurchase::STATUS_PAID])
            ->sum('votes_count');

        $remainingSlots = SpotlightWeek::maxPurchasedVotes() - $existingTotal;

        if ($remainingSlots <= 0) {
            return [
                'success' => false,
                'message' => 'Maximum support reached for this nominee. No more votes can be purchased.',
            ];
        }

        if ($package->votes_count > $remainingSlots) {
            return [
                'success' => false,
                'message' => "This package adds {$package->votes_count} votes but only {$remainingSlots} slots remain (100-vote cap).",
            ];
        }

        // 3. Create the purchase request
        try {
            $purchase = DB::transaction(function () use ($user, $nominee, $package) {
                return SpotlightVotePurchase::create([
                    'spotlight_week_nominee_id' => $nominee->id,
                    'user_id'                   => $user->id,
                    'spotlight_vote_package_id' => $package->id,
                    'package'                   => $package->slug,
                    'votes_count'               => $package->votes_count,
                    'amount_paid'               => $package->price,
                    'status'                    => SpotlightVotePurchase::STATUS_PENDING,
                ]);
            });

            // 4. Send email notification to admin
            try {
                Mail::to(config('mail.admin_email', 'admin@example.com'))
                    ->send(new SpotlightVotePurchaseRequestAdminNotification($purchase));
            } catch (Exception $e) {
                Log::warning('SpotlightVotePurchase: Failed to send admin email notification', [
                    'purchase_id' => $purchase->id,
                    'error'       => $e->getMessage(),
                ]);
            }

            Log::info('SpotlightVotePurchase: Purchase requested', [
                'user_id'    => $user->id,
                'nominee_id' => $nominee->id,
                'package'    => $package->slug,
                'votes'      => $package->votes_count,
                'amount'     => $package->price,
            ]);

            return [
                'success'  => true,
                'message'  => "Purchase request submitted for {$package->votes_count} vote(s) at \${$package->price}. Pending admin approval.",
                'purchase' => $purchase->load(['package', 'nominee.week', 'nominee.spotlightable']),
            ];
        } catch (Exception $e) {
            Log::error('SpotlightVotePurchase: Failed to create purchase', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to submit purchase request. Please try again.',
            ];
        }
    }

    /**
     * Admin approves a pending purchase.
     *
     * Changes status from 'pending' → 'approved'.
     * Does NOT credit votes yet — payment must be completed first.
     *
     * @return array{success: bool, message: string}
     */
    public function approvePurchase(SpotlightVotePurchase $purchase, User $admin): array
    {
        if (! $purchase->isPending()) {
            return [
                'success' => false,
                'message' => "Purchase is already '{$purchase->status}'. Only pending purchases can be approved.",
            ];
        }

        $purchase->update([
            'status'      => SpotlightVotePurchase::STATUS_APPROVED,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        Log::info('SpotlightVotePurchase: Purchase approved (awaiting payment)', [
            'purchase_id' => $purchase->id,
            'admin_id'    => $admin->id,
        ]);

        return [
            'success' => true,
            'message' => 'Purchase approved. The user can now proceed to payment.',
        ];
    }

    /**
     * Create a Stripe Checkout session for an approved purchase.
     *
     * @return array{success: bool, message: string, checkout_url?: string, session_id?: string}
     */
    public function initiatePayment(SpotlightVotePurchase $purchase, User $user): array
    {
        if (! $purchase->isPayable()) {
            return [
                'success' => false,
                'message' => "This purchase is not ready for payment. Status: {$purchase->status}",
            ];
        }

        if ($purchase->user_id !== $user->id) {
            return [
                'success' => false,
                'message' => 'You can only pay for your own purchases.',
            ];
        }

        try {
            $nominee = $purchase->nominee;
            $spotlightable = $nominee?->spotlightable;

            // Build a descriptive name for the line item
            $isArtist = $spotlightable && $nominee->spotlightable_type === \App\Models\ArtistSpotlight::class;
            if ($spotlightable) {
                if ($isArtist) {
                    $spotlightName = $spotlightable->artist_stage_name ?? $spotlightable->full_legal_name;
                } else {
                    $spotlightName = $spotlightable->business_name ?? $spotlightable->owner_founder_name;
                }
            } else {
                $spotlightName = 'Spotlight Nominee';
            }

            // Resolve package name safely (supports both model relationship and string slug)
            $packageName = $purchase->package?->name ?? $purchase->package;

            $checkoutSession = $this->stripeService->createCheckoutSession([
                'order_id'       => $purchase->id,
                'order_number'   => 'VOTE-' . $purchase->id,
                'amount'         => (float) $purchase->amount_paid,
                'customer_email' => $user->email,
                'line_items'     => [
                    [
                        'name'     => $packageName . ' - ' . $purchase->votes_count . ' Vote(s) for ' . $spotlightName,
                        'quantity' => 1,
                        'price'    => (float) $purchase->amount_paid,
                    ],
                ],
                'metadata' => [
                    'type'        => 'vote_purchase',
                    'purchase_id' => (string) $purchase->id,
                ],
            ]);

            // Store the Stripe session ID on the purchase
            $purchase->update([
                'stripe_checkout_session_id' => $checkoutSession->id,
            ]);

            return [
                'success'       => true,
                'message'       => 'Redirecting to payment...',
                'checkout_url'  => $checkoutSession->url,
                'session_id'    => $checkoutSession->id,
            ];
        } catch (Exception $e) {
            Log::error('SpotlightVotePurchase: Stripe checkout error', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again.',
            ];
        }
    }

    /**
     * Handle successful payment via webhook.
     *
     * Credits the votes to the nominee and marks purchase as 'paid'.
     */
    public function completePayment(string $checkoutSessionId, string $paymentIntentId): ?SpotlightVotePurchase
    {
        $purchase = SpotlightVotePurchase::where('stripe_checkout_session_id', $checkoutSessionId)
            ->where('status', SpotlightVotePurchase::STATUS_APPROVED)
            ->first();

        if (! $purchase) {
            Log::warning('SpotlightVotePurchase: No payable purchase found for session', [
                'session_id' => $checkoutSessionId,
            ]);
            return null;
        }

        DB::transaction(function () use ($purchase, $paymentIntentId) {
            // Credit votes to nominee
            $purchase->nominee->addPaidVotes($purchase->votes_count);

            // Mark purchase as paid
            $purchase->update([
                'status'                    => SpotlightVotePurchase::STATUS_PAID,
                'stripe_payment_intent_id'  => $paymentIntentId,
                'paid_at'                   => now(),
            ]);
        });

        Log::info('SpotlightVotePurchase: Payment completed, votes credited', [
            'purchase_id' => $purchase->id,
            'nominee_id'  => $purchase->nominee->id,
            'votes'       => $purchase->votes_count,
        ]);

        return $purchase->fresh(['nominee', 'package']);
    }

    /**
     * Admin refunds a paid purchase — removes credited votes.
     *
     * @return array{success: bool, message: string}
     */
    public function refundPurchase(SpotlightVotePurchase $purchase, User $admin, ?string $notes = null): array
    {
        if (! $purchase->isPaid()) {
            return [
                'success' => false,
                'message' => "Can only refund 'paid' purchases. Current status: {$purchase->status}",
            ];
        }

        DB::transaction(function () use ($purchase, $admin, $notes) {
            // Remove votes from nominee
            $purchase->nominee->removePaidVotes($purchase->votes_count);

            // Mark as refunded
            $purchase->update([
                'status'      => SpotlightVotePurchase::STATUS_REFUNDED,
                'approved_by' => $admin->id,
                'admin_notes' => $notes,
            ]);
        });

        Log::info('SpotlightVotePurchase: Purchase refunded, votes removed', [
            'purchase_id' => $purchase->id,
            'admin_id'    => $admin->id,
        ]);

        return [
            'success' => true,
            'message' => "{$purchase->votes_count} vote(s) removed from nominee (refunded).",
        ];
    }

    /**
     * Cancel a pending/approved purchase (user or admin).
     */
    public function cancelPurchase(SpotlightVotePurchase $purchase, User $user, ?string $reason = null): array
    {
        if (! in_array($purchase->status, [SpotlightVotePurchase::STATUS_PENDING, SpotlightVotePurchase::STATUS_APPROVED])) {
            return [
                'success' => false,
                'message' => "Purchase with status '{$purchase->status}' cannot be cancelled.",
            ];
        }

        $purchase->update([
            'status'      => SpotlightVotePurchase::STATUS_CANCELLED,
            'admin_notes' => $reason ? ($purchase->admin_notes ? $purchase->admin_notes . "\nCancelled: " . $reason : 'Cancelled: ' . $reason) : $purchase->admin_notes,
        ]);

        Log::info('SpotlightVotePurchase: Purchase cancelled', [
            'purchase_id' => $purchase->id,
            'user_id'     => $user->id,
        ]);

        return [
            'success' => true,
            'message' => 'Purchase cancelled successfully.',
        ];
    }
}
