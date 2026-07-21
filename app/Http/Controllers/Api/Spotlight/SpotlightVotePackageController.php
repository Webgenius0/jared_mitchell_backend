<?php

namespace App\Http\Controllers\Api\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\Spotlight\SpotlightVotePackage;
use App\Models\Spotlight\SpotlightWeek;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SpotlightVotePackageController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/spotlight/vote-packages
     *
     * List all active vote packages available for purchase.
     * Authenticated users see the available packages with prices.
     */
    public function index(): JsonResponse
    {
        $packages = SpotlightVotePackage::active()
            ->ordered()
            ->get()
            ->map(function ($pkg) {
                return [
                    'id'          => $pkg->id,
                    'name'        => $pkg->name,
                    'slug'        => $pkg->slug,
                    'votes_count' => $pkg->votes_count,
                    'price'       => (float) $pkg->price,
                    'description' => $pkg->description,
                ];
            });

        return $this->success('Vote packages retrieved successfully.', [
            'packages'        => $packages,
            'max_paid_votes'  => SpotlightWeek::maxPurchasedVotes(),
        ]);
    }
}
