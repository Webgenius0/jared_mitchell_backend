<?php

namespace App\Services;

use App\Models\PricingPlan;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class StripeProductSyncService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Create (or update) a Stripe Product and a recurring Price for the given pricing plan.
     *
     * Both Stripe calls complete BEFORE updating the DB to avoid partial-failure states
     * where the Stripe product is created but no price exists (or the DB has stale IDs).
     *
     * @return array{stripe_product_id: string, stripe_price_id: string}
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function sync(PricingPlan $plan): array
    {
        // 1. Create / update the Product on Stripe
        $product = $this->syncProduct($plan);

        // 2. Create the Price on Stripe (the old one is deactivated inside syncPrice)
        $price = $this->syncPrice($plan, $product->id);

        // 3. Both Stripe calls succeeded — now persist the IDs locally
        $plan->update([
            'stripe_product_id' => $product->id,
            'stripe_price_id'   => $price->id,
        ]);

        return [
            'stripe_product_id' => $product->id,
            'stripe_price_id'   => $price->id,
        ];
    }

    /**
     * Create or retrieve matching Stripe Product.
     */
    protected function syncProduct(PricingPlan $plan): Product
    {
        // Cast DB integer (0/1) to real boolean for Stripe API
        $active = (bool) $plan->is_visible;

        // If the plan already has a product ID, try to update it
        if ($plan->stripe_product_id) {
            try {
                return Product::update($plan->stripe_product_id, [
                    'name'        => $plan->plan_name,
                    'description' => $plan->best_for,
                    'active'      => $active,
                    'metadata'    => [
                        'pricing_plan_id' => (string) $plan->id,
                    ],
                ]);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Product doesn't exist on Stripe anymore — create a new one
            }
        }

        return Product::create([
            'name'        => $plan->plan_name,
            'description' => $plan->best_for,
            'active'      => $active,
            'metadata'    => [
                'pricing_plan_id' => (string) $plan->id,
            ],
        ]);
    }

    /**
     * Create a recurring Price for the plan.
     *
     * Cashier works best with recurring prices. If no interval is defined
     * on the plan, we default to "month".
     *
     * IMPORTANT: The new price is created FIRST, then the old price is deactivated.
     * This prevents a window where the plan has zero active prices on Stripe.
     */
    protected function syncPrice(PricingPlan $plan, string $productId): Price
    {
        $interval = $this->parseInterval($plan->price_suffix);

        // Build metadata for lookup
        $metadata = [
            'pricing_plan_id' => (string) $plan->id,
            'interval'        => $interval,
        ];

        $unitAmount = (int) round($plan->price * 100); // Convert dollars to cents

        // 1. Create the new price FIRST
        $price = Price::create([
            'product'     => $productId,
            'unit_amount' => $unitAmount,
            'currency'    => strtolower(config('cashier.currency', 'usd')),
            'recurring'   => [
                'interval'       => $interval,
                'interval_count' => 1,
            ],
            'active'      => true,
            'metadata'    => $metadata,
        ]);

        // 2. Deactivate the OLD price AFTER the new one exists.
        //    If creation failed, an exception would have been thrown above,
        //    so the old price remains active and existing subscriptions are unaffected.
        if ($plan->stripe_price_id) {
            try {
                Price::update($plan->stripe_price_id, [
                    'active'   => false,
                    'metadata' => array_merge($metadata, ['replaced_by' => $price->id]),
                ]);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Old price doesn't exist on Stripe — that's fine
            }
        }

        return $price;
    }

    /**
     * Parse price_suffix to extract a Stripe-compatible recurring interval.
     *
     * Supports: /month, /year, /week, /day, weekly, monthly, yearly, annual
     * Defaults to "month".
     */
    protected function parseInterval(?string $suffix): string
    {
        if (empty($suffix)) {
            return 'month';
        }

        $lower = strtolower(trim($suffix));

        if (str_contains($lower, 'year') || str_contains($lower, 'annual')) {
            return 'year';
        }

        if (str_contains($lower, 'week')) {
            return 'week';
        }

        if (str_contains($lower, 'day')) {
            return 'day';
        }

        return 'month';
    }
}
