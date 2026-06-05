<?php

namespace App\Http\Controllers\Api\Cms;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\CmsContentResource;
use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BossBeginingsController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/artist-spotlight
     *
     * Returns all CMS content for the artist spotlight page, keyed by section.
     */
    public function index(): JsonResponse
    {
        $cmsData = CMS::where('page', CmsPage::BOSS_BEGINNINGS)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof CmsSection ? $item->section->value : $item->section;
            });

        return $this->success(
            'Boss Beginnings page CMS content retrieved successfully.',
            CmsContentResource::collection($cmsData)->resolve(),
            200
        );
    }
}
