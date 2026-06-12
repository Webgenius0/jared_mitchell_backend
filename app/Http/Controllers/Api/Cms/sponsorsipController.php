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

class SponsorsipController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/sponsorsip  
     *
     * Returns all CMS content for the sponsorship page, keyed by section.
     */
    public function index(): JsonResponse
    {
        $cmsData = CMS::where('page', CmsPage::SPONSORSHIP)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof CmsSection ? $item->section->value : $item->section;
            });

        $shonsors = CMS::where('page', CmsPage::HOME)
            ->where('section', CmsSection::PARTNERS)
            ->get();
        // add $shonsors in $cmsData
        $cmsData['partners'] = $shonsors;

        $newletter = CMS::where('page', CmsPage::HOME)
            ->where('section', CmsSection::NEWSLETTER)
            ->first();
        if ($newletter) {
            $cmsData['newsletter'] = $newletter;
        }

        return $this->success(
            'Sponsorship page CMS content retrieved successfully.',
            CmsContentResource::collection($cmsData)->resolve(),
            200
        );
    }
}
