<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class PricingController extends Controller
{
    use ApiResponse;
    public function index()
    {
        try {
            $plans = PricingPlan::query()
                ->with([
                    'featureGroups.items'
                ])
                ->withCount('featureGroups')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(function (PricingPlan $plan) {
                    return [
                        'id' => $plan->id,
                        'plan_name' => $plan->plan_name,
                        'price' => $plan->price,
                        'price_suffix' => $plan->price_suffix,
                        'badge_text' => $plan->badge_text,
                        'is_featured' => $plan->is_featured,
                        'is_visible' => $plan->is_visible,
                        'groups_count' => $plan->feature_groups_count,
                        'feature_groups' => $plan->featureGroups->map(function ($group) {
                            return [
                                'id' => $group->id,
                                'title' => $group->title,
                                'items' => $group->items->map(function ($item) {
                                    return [
                                        'id' => $item->id,
                                        'feature_text' => $item->feature_text,
                                    ];
                                })->values(),
                            ];
                        })->values(),
                    ];
                });
            return $this->success('Pricing plans retrieved successfully.', [
                'plans' => $plans,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve pricing plans: ' . $e->getMessage());
            return $this->error('Failed to retrieve pricing plans. Please try again later.');
        }
    }
}
