<?php

namespace App\Http\Controllers\Api\Cms;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\CmsContentResource;
use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CmsRoundsController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/osi-rounds
     *
     * Returns all CMS content for the Rounds page, keyed by section.
     * The rounds data lives in the "rounds" container section under
     * metadata: { block: {...}, rounds: [...] }.
     */
    public function index(): JsonResponse
    {
        $cmsData = CMS::where('page', CmsPage::ROUNDS)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof CmsSection ? $item->section->value : $item->section;
            });

        $sponsors = CMS::where('page', CmsPage::HOME)
            ->where('section', CmsSection::PARTNERS)
            ->get();
        // add sponsors in $cmsData
        $cmsData['partners'] = $sponsors;

        $newsletter = CMS::where('page', CmsPage::HOME)
            ->where('section', CmsSection::NEWSLETTER)
            ->first();
        if ($newsletter) {
            $cmsData['newsletter'] = $newsletter;
        }

        return $this->success(
            'Rounds page CMS content retrieved successfully.',
            CmsContentResource::collection($cmsData)->resolve(),
            200
        );
    }
}
