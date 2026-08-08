<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\EventRegistrationService;
use App\Services\OrderService;
use App\Services\Spotlight\SpotlightVotePurchaseService;
use App\Services\StripeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookHandler;
use Stripe\Event;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected OrderService $orderService,
        protected EventRegistrationService $eventRegistrationService,
        protected SpotlightVotePurchaseService $votePurchaseService,
    ) {
    }

    /**
     * POST /api/webhooks/stripe
     *
     * Single Stripe webhook endpoint that handles:
     *   - Subscription events → Cashier's WebhookController
     *   - Order / event / vote payments → custom handlers
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $event = $this->stripeService->constructWebhookEvent($payload, $sigHeader);

        if (!$event) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 1. Process subscription-related events via Cashier
        //    Cashier handles: customer.subscription.*, invoice.*, customer.*,
        //    and checkout.session.completed for subscription checkouts.
        //
        //    IMPORTANT: No try/catch here — if Cashier fails, Stripe gets a 5xx
        //    response and will RETRY the webhook. A silent catch would permanently
        //    lose the subscription.
        app(CashierWebhookHandler::class)->handleWebhook($request);

        // 2. Process custom application events (orders, event registrations, votes)
        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutCompleted($event),
                'checkout.session.expired' => $this->handleCheckoutExpired($event),
                default => null,
            };
        } catch (Exception $e) {
            Log::error('Stripe webhook: custom handler failed', [
                'event_type' => $event->type ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Handle checkout.session.completed
     *
     * When a Stripe Checkout session is completed successfully, handle
     * the appropriate action based on the metadata type.
     */
    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;

        $type = $session->metadata->type ?? null;

        // Custom subscription handler: manually save subscription data without Cashier Webhook
        if ($session->mode === 'subscription') {
            $this->handleSubscriptionCheckoutCompleted($session);
            return;
        }

        match ($type) {
            'event_registration' => $this->handleEventRegistrationPayment($session),
            'vote_purchase' => $this->handleVotePurchasePayment($session),
            default => $this->handleOrderPayment($session),
        };
    }

    /**
     * Handle payment for event registration.
     */
    private function handleEventRegistrationPayment($session): void
    {
        $registrationId = $session->metadata->registration_id ?? null;
        if ($registrationId) {
            $this->eventRegistrationService->markAsPaid(
                $registrationId,
                $session->id,
                $session->payment_intent
            );
        }
    }

    /**
     * Handle successful subscription checkout and manually store the data.
     */
    private function handleSubscriptionCheckoutCompleted($session): void
    {
        $customerId = $session->customer;
        $user = \App\Models\User::where('stripe_id', $customerId)->first();

        if (!$user && $session->client_reference_id) {
            $user = \App\Models\User::find($session->client_reference_id);
            if ($user && !$user->stripe_id) {
                $user->stripe_id = $customerId;
                $user->save();
            }
        }

        if (!$user) {
            Log::error('Stripe webhook: User not found for subscription', ['customer' => $customerId]);
            return;
        }

        $subscriptionId = $session->subscription;
        if (!$subscriptionId) {
            return;
        }

        try {
            $stripeSubscription = \Stripe\Subscription::retrieve($subscriptionId);

            $isSinglePrice = count($stripeSubscription->items->data) === 1;
            $firstItem = $stripeSubscription->items->data[0];

            $trialEndsAt = $stripeSubscription->trial_end
                ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->trial_end)
                : null;

            $subscription = $user->subscriptions()->updateOrCreate([
                'stripe_id' => $stripeSubscription->id,
            ], [
                'type' => 'default',
                'stripe_status' => $stripeSubscription->status,
                'stripe_price' => $isSinglePrice ? $firstItem->price->id : null,
                'quantity' => $isSinglePrice ? ($firstItem->quantity ?? 1) : null,
                'trial_ends_at' => $trialEndsAt,
                'ends_at' => null,
            ]);

            foreach ($stripeSubscription->items->data as $item) {
                $subscription->items()->updateOrCreate([
                    'stripe_id' => $item->id,
                ], [
                    'stripe_product' => $item->price->product,
                    'stripe_price' => $item->price->id,
                    'quantity' => $item->quantity ?? null,
                ]);
            }

            Log::info('Stripe webhook: custom subscription stored successfully', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Stripe webhook: failed to manually store subscription', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle payment for spotlight vote purchase.
     *
     * Credits the purchased votes to the nominee immediately.
     */
    private function handleVotePurchasePayment($session): void
    {
        $purchaseId = $session->metadata->purchase_id ?? null;

        if (!$purchaseId) {
            return;
        }

        $this->votePurchaseService->completePayment(
            $session->id,
            $session->payment_intent
        );
    }

    /**
     * Handle payment for a regular product order.
     */
    private function handleOrderPayment($session): void
    {
        $orderId = $session->metadata->order_id ?? null;

        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);

        if (!$order) {
            return;
        }

        // Only update if the order is still pending/unpaid
        if ($order->payment_status !== Order::PAYMENT_UNPAID) {
            return;
        }

        $this->orderService->updatePaymentStatus(
            $order->id,
            Order::PAYMENT_PAID,
            $session->payment_intent  // Stripe PaymentIntent ID
        );
    }

    /**
     * Handle checkout.session.expired
     *
     * When a Stripe Checkout session expires without completion, handle
     * the appropriate cancellation based on the metadata type.
     */
    private function handleCheckoutExpired(Event $event): void
    {
        $session = $event->data->object;

        $type = $session->metadata->type ?? null;

        match ($type) {
            'event_registration' => $this->handleEventRegistrationExpired($session),
            'vote_purchase' => $this->handleVotePurchaseExpired($session),
            default => $this->handleOrderExpired($session),
        };
    }

    /**
     * Handle expired event registration payment session.
     */
    private function handleEventRegistrationExpired($session): void
    {
        $registrationId = $session->metadata->registration_id ?? null;
        if ($registrationId) {
            $this->eventRegistrationService->cancel($registrationId, 'Payment session expired.');
        }
    }

    /**
     * Handle expired vote purchase payment session.
     */
    private function handleVotePurchaseExpired($session): void
    {
        // No action needed — purchase stays in 'approved' status
        // User can request a new payment link
    }

    /**
     * Handle expired order payment session.
     */
    private function handleOrderExpired($session): void
    {
        $orderId = $session->metadata->order_id ?? null;

        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);

        if (!$order) {
            return;
        }

        // Only cancel if the order is still pending/unpaid
        if ($order->payment_status !== Order::PAYMENT_UNPAID) {
            return;
        }

        $this->orderService->cancel(
            $order->user_id,
            $order->id,
            'Payment session expired.'
        );
    }
}
