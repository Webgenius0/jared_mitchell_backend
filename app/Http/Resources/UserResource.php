<?php

namespace App\Http\Resources;

use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subscription = $this->subscription('default');

        $subscriptionData = null;

        if ($subscription && $subscription->valid()) {
            // Look up the pricing plan that matches this subscription's stripe price
            $plan = PricingPlan::where('stripe_price_id', $subscription->stripe_price)->first();

            $subscriptionData = [
                'status'            => $subscription->stripe_status,
                'on_trial'          => $subscription->onTrial(),
                'on_grace_period'   => $subscription->onGracePeriod(),
                'canceled'          => $subscription->canceled(),
                'plan_name'         => $plan?->plan_name,
                'plan_price'        => $plan ? (float) $plan->price : null,
                'plan_price_suffix' => $plan?->price_suffix,
                'trial_ends_at'     => $subscription->trial_ends_at?->toISOString(),
                'ends_at'           => $subscription->ends_at?->toISOString(),
            ];
        }

        return [
            'id' => $this->id,
            'email' => $this->email ?? '',
            'status' => $this->status ?? 'inactive',
            'role' => $this->getRoleNames()->first() ?? null,

            // subscription
            'subscription' => $subscriptionData,

            // profile resource
            'profile' => $this->profile
                ? new ProfileResource($this->profile)
                : [
                    'id' => null,
                    'name' => '',
                    'username' => '',
                    'slug' => '',
                    'avatar' => asset('admin/default/user.jpg'),
                ],

            'artist_category' => $this->artistCategory ? [
                'id' => $this->artistCategory->id,
                'name' => $this->artistCategory->name,
                'slug' => $this->artistCategory->slug,
            ] : null,
            'business_category' => $this->businessCategory ? [
                'id' => $this->businessCategory->id,
                'name' => $this->businessCategory->name,
                'slug' => $this->businessCategory->slug,
            ] : null,
        ];
    }
}
