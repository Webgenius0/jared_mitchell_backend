<?php

namespace App\Http\Controllers\Api\Cms;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Http\Controllers\Api\Contest\BossBeginningWinnerController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\CmsContentResource;
use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CmsHomePageController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/homepage
     *
     * Returns all CMS content for the homepage, keyed by section.
     */
    public function index(): JsonResponse
    {
        $cmsData = CMS::where('page', CmsPage::HOME)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof CmsSection ? $item->section->value : $item->section;
            });

        // Fetch past 6 month boss beginning winners
        $bossBeginningController = app(BossBeginningWinnerController::class);
        $winnersResponse = $bossBeginningController->pastWinners();
        $pastWinners = $winnersResponse->getStatusCode() === 200
            ? $winnersResponse->getData(true)['data']['winners'] ?? []
            : [];

        $resolvedData = CmsContentResource::collection($cmsData)->resolve();

        // Inject into metadata
        if (isset($resolvedData['past_6_month_boss_beginnings_highlight'])) {
            $resolvedData['past_6_month_boss_beginnings_highlight']['metadata'] = $pastWinners;
        }

        return $this->success(
            'Homepage CMS content retrieved successfully.',
            $resolvedData
        );
    }
}
