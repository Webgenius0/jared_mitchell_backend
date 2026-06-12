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
            ->mapWithKeys(function ($item) {
                return [$item->section->value => (new CmsContentResource($item))->resolve()];
            });

        $shonsors = CMS::where('page', CmsPage::HOME)
            ->where('section', CmsSection::PARTNERS)
            ->get();
        
        $cmsData['partners'] = (new CmsContentResource($shonsors))->resolve();

        $newletter = CMS::where('page', CmsPage::HOME)
            ->where('section', CmsSection::NEWSLETTER)
            ->first();
        if ($newletter) {
            $cmsData['newsletter'] = (new CmsContentResource($newletter))->resolve();
        }

        return $this->success(
            'Events page CMS content retrieved successfully.',
            $cmsData,
            200
        );
    }
}
