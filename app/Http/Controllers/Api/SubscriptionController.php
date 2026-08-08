<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * Get all active pricing plans with their features.
     */
    public function index(): JsonResponse
    {
        $plans = PricingPlan::with(['featureGroups.items'])
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $plans,
        ]);
    }

    /**
     * Create a Stripe Checkout session for a subscription.
     */
    public function checkout(Request $request): JsonResponse
    {
        // dd($request->all());
        $request->validate([
            'pricing_plan_id' => ['required', 'exists:pricing_plans,id'],
            'success_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
        ]);

        $plan = PricingPlan::findOrFail($request->pricing_plan_id);
        // dd($plan);

        if (!$plan->stripe_price_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This plan is not configured for Stripe subscriptions.',
            ], 400);
        }

        $user = auth('api')->user();

        // Create the Stripe Checkout Session
        $checkout = $user->newSubscription('default', $plan->stripe_price_id)
            ->checkout([
                'success_url' => $request->success_url ?? config('app.frontend_url') . '/subscription/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $request->cancel_url ?? config('app.frontend_url') . '/subscription/cancel',
            ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'checkout_url' => $checkout->url,
            ],
        ]);
    }

    /**
     * Get the current user's subscription status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $subscription = $user->subscription('default');
        // dd($subscription);

        // if (!$subscription) {
        //     return response()->json([
        //         'status' => 'success',
        //         'data' => [
        //             'is_subscribed' => false,
        //             'details' => null,
        //         ],
        //     ]);
        // }

        return response()->json([
            'status' => 'success',
            'data' => [
                'is_subscribed' => $subscription->valid(),
                'details' => [
                    'name' => $subscription->name,
                    'stripe_id' => $subscription->stripe_id,
                    'stripe_status' => $subscription->stripe_status,
                    'stripe_price' => $subscription->stripe_price,
                    'quantity' => $subscription->quantity,
                    'trial_ends_at' => $subscription->trial_ends_at,
                    'ends_at' => $subscription->ends_at,
                    'on_grace_period' => $subscription->onGracePeriod(),
                    'canceled' => $subscription->canceled(),
                ],
            ],
        ]);
    }

    /**
     * Cancel the user's current subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->valid()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have an active subscription.',
            ], 400);
        }

        $subscription->cancel();

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription canceled successfully. It will remain active until the end of the billing period.',
        ]);
    }

    /**
     * Resume a canceled subscription that is on a grace period.
     */
    public function resume(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->onGracePeriod()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a canceled subscription on a grace period.',
            ], 400);
        }

        $subscription->resume();

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription resumed successfully.',
        ]);
    }

    /**
     * Swap the user's active subscription to a new plan.
     */
    public function swap(Request $request): JsonResponse
    {
        $request->validate([
            'pricing_plan_id' => ['required', 'exists:pricing_plans,id'],
        ]);

        $user = $request->user();
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->valid()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have an active subscription to change.',
            ], 400);
        }

        $plan = PricingPlan::findOrFail($request->pricing_plan_id);

        if (!$plan->stripe_price_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'The selected plan is not configured for Stripe subscriptions.',
            ], 400);
        }

        if ($subscription->hasPrice($plan->stripe_price_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are already on this plan.',
            ], 400);
        }

        // Swap the plan
        $subscription->swap($plan->stripe_price_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription plan successfully updated.',
        ]);
    }

    /**
     * Get the URL for the Stripe Customer Billing Portal.
     */
    public function billingPortal(Request $request): JsonResponse
    {
        $user = $request->user();

        // If the user doesn't have a Stripe Customer ID yet, they haven't subscribed
        if (!$user->hasStripeId()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a billing profile.',
            ], 400);
        }

        $returnUrl = $request->return_url ?? config('app.frontend_url') . '/dashboard';

        // Get the billing portal URL
        $portalUrl = $user->billingPortalUrl($returnUrl);

        return response()->json([
            'status' => 'success',
            'data' => [
                'portal_url' => $portalUrl,
            ],
        ]);
    }
}
