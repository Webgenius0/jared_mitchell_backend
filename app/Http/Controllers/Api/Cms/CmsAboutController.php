<?php

namespace App\Http\Controllers\Api\Cms;

use App\Enums\CmsPage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\CmsContentResource;
use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CmsAboutController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/about
     *
     * Returns all CMS content for the about page, keyed by section.
     */
    public function index(): JsonResponse
    {
        $cmsData = CMS::where('page', CmsPage::ABOUT)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof \App\Enums\CmsSection ? $item->section->value : $item->section;
            });

        return $this->success(
            'About page CMS content retrieved successfully.',
            CmsContentResource::collection($cmsData)->resolve()
        );
    }
}
