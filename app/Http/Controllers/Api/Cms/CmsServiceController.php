<?php

namespace App\Http\Controllers\Api\Cms;

use App\Enums\CmsPage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\CmsContentResource;
use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CmsServiceController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/services
     *
     * Returns all CMS content for the services page, keyed by section.
     */
    public function index(): JsonResponse
    {
        $cmsData = CMS::where('page', CmsPage::SERVICES)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof \App\Enums\CmsSection ? $item->section->value : $item->section;
            });

        return $this->success(
            'Services page CMS content retrieved successfully.',
            CmsContentResource::collection($cmsData)->resolve()
        );
    }
}
