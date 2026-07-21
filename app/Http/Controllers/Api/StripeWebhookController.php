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
use Stripe\Event;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected OrderService  $orderService,
        protected EventRegistrationService $eventRegistrationService,
        protected SpotlightVotePurchaseService $votePurchaseService,
    ) {}

    /**
     * POST /api/webhooks/stripe
     *
     * Handle incoming Stripe webhook events.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $event = $this->stripeService->constructWebhookEvent($payload, $sigHeader);

        if (!$event) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutCompleted($event),
                'checkout.session.expired' => $this->handleCheckoutExpired($event),
                default => null,
            };
        } catch (Exception $e) {
            // Log the error but still return 200 to acknowledge receipt
            report($e);
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
     * Handle payment for spotlight vote purchase.
     *
     * Credits the purchased votes to the nominee immediately.
     */
    private function handleVotePurchasePayment($session): void
    {
        $purchaseId = $session->metadata->purchase_id ?? null;

        if (! $purchaseId) {
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

        if (! $orderId) {
            return;
        }

        $order = Order::find($orderId);

        if (! $order) {
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

        if (! $orderId) {
            return;
        }

        $order = Order::find($orderId);

        if (! $order) {
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
