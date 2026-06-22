<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\StripeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected OrderService  $orderService
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
                'checkout.session.expired'   => $this->handleCheckoutExpired($event),
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
     * When a Stripe Checkout session is completed successfully, mark the
     * order as paid and confirmed.
     */
    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;

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
     * When a Stripe Checkout session expires without completion, cancel the order.
     */
    private function handleCheckoutExpired(Event $event): void
    {
        $session = $event->data->object;

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
