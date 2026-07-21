<?php

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session for an order.
     *
     * @param array{
     *     order_id: int,
     *     order_number: string,
     *     amount: float,
     *     customer_email: string|null,
     *     line_items: array<int, array{name: string, quantity: int, price: float}>,
     *     metadata: array<string, string>,
     * } $data
     * @return Session
     *
     * @throws ApiErrorException
     */
    public function createCheckoutSession(array $data): Session
    {
        $lineItems = [];

        foreach ($data['line_items'] as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'usd',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => (int) round($item['price'] * 100), // cents
                ],
                'quantity' => $item['quantity'],
            ];
        }

        return Session::create([
            'mode' => 'payment',
            'success_url' => $this->buildSuccessUrl($data['order_number']),
            'cancel_url' => $this->buildCancelUrl($data['order_number']),
            'customer_email' => $data['customer_email'] ?? null,
            'client_reference_id' => (string) $data['order_id'],
            'line_items' => $lineItems,
            'metadata' => array_merge($data['metadata'], [
                'order_id' => (string) $data['order_id'],
                'order_number' => $data['order_number'],
            ]),
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => (string) $data['order_id'],
                    'order_number' => $data['order_number'],
                ],
            ],
        ]);
    }

    /**
     * Construct and verify a Stripe webhook event from the raw payload.
     *
     * @param string $payload      Raw JSON body
     * @param string $sigHeader    Value of the stripe-signature header
     *
     * @return Event|null
     */
    public function constructWebhookEvent(string $payload, string $sigHeader): ?Event
    {
        $endpointSecret = config('stripe.webhook_secret');

        if (empty($endpointSecret)) {
            return null;
        }

        try {
            return Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException|ApiErrorException $e) {
            return null;
        }
    }

    /**
     * Build the success redirect URL with the order number.
     */
    private function buildSuccessUrl(string $orderNumber): string
    {
        $base = config('stripe.success_url');

        if (empty($base)) {
            $base = config('app.frontend_url') . '/payment/success';
        }

        return $base . '?order_number=' . $orderNumber . '&session_id={CHECKOUT_SESSION_ID}';
    }

    /**
     * Build the cancel redirect URL with the order number.
     */
    private function buildCancelUrl(string $orderNumber): string
    {
        $base = config('stripe.cancel_url');

        if (empty($base)) {
            $base = config('app.frontend_url') . '/payment/cancel';
        }

        return $base . '?order_number=' . $orderNumber;
    }
}
