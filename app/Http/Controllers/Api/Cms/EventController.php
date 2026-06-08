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

class EventController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/events
     *
     * Returns all CMS content for the events page, keyed by section.
     */
    public function index(): JsonResponse
    {
        $cmsData = CMS::where('page', CmsPage::EVENTS)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof CmsSection ? $item->section->value : $item->section;
            });

        return $this->success(
            'Events page CMS content retrieved successfully.',
            CmsContentResource::collection($cmsData)->resolve(),
            200
        );
    }
}
