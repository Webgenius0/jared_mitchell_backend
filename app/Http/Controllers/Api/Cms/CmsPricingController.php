<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\PricingPlanResource;
use App\Models\PricingPlan;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CmsPricingController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/pricing
     *
     * Returns all visible pricing plans ordered by sort_order,
     * with feature groups and their items eager-loaded.
     * Cached for 5 minutes; invalidated when admin saves/toggles a plan.
     */
    public function index(): JsonResponse
    {
        $plans = Cache::remember('api:cms:pricing:index', 300, function () {
            return PricingPlan::where('is_visible', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->with([
                    'featureGroups' => fn ($q) => $q->orderBy('sort_order')
                        ->select(['id', 'price_plan_id', 'title', 'sort_order']),
                    'featureGroups.items' => fn ($q) => $q->orderBy('sort_order')
                        ->select(['id', 'feature_group_id', 'feature_text', 'sort_order']),
                ])
                ->get();
        });

        return $this->success(
            'Pricing plans retrieved.',
            PricingPlanResource::collection($plans)->resolve()
        );
    }
}
