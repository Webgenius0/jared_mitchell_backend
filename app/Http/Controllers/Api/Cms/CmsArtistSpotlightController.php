<?php

namespace App\Http\Controllers\Api\Cms;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\CmsContentResource;
use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CmsArtistSpotlightController extends Controller
{
    use ApiResponse;

    /**
     * Section order matching the Blade view (_artist_spotlight.blade.php) sequence.
     */
    private function artistSpotlightSectionOrder(): array
    {
        return [
            CmsSection::ARTIST_SPOTLIGHT_HERO->value,
            CmsSection::ARTIST_SPOTLIGHT_VIDEO->value,
            CmsSection::ARTIST_SPOTLIGHT_LIST->value,
            CmsSection::ARTIST_SPOTLIGHT_HIGHLIGHTS->value,
            CmsSection::ARTIST_SPOTLIGHT_LADDER->value,
            CmsSection::ARTIST_SPOTLIGHT_JOIN->value,
            CmsSection::ARTIST_SPOTLIGHT_WHY_EXISTS->value,
        ];
    }

    public function index(): JsonResponse
    {
        $cmsData = CMS::where('page', CmsPage::ARTIST_SPOTLIGHT)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof CmsSection ? $item->section->value : $item->section;
            });

        // Reorder sections to match the Blade view sequence
        $orderedCmsData = collect();
        foreach ($this->artistSpotlightSectionOrder() as $sectionKey) {
            if ($cmsData->has($sectionKey)) {
                $orderedCmsData->put($sectionKey, $cmsData->get($sectionKey));
            }
        }

        // Sponsors / Partners (appended after main sections, same as blade)
        $sponsors = CMS::where('page', CmsPage::HOME)
            ->where('section', CmsSection::PARTNERS)
            ->get();
        $orderedCmsData->put('partners', $sponsors);

        // Newsletter (appended at the end)
        $newsletter = CMS::where('page', CmsPage::HOME)
            ->where('section', CmsSection::NEWSLETTER)
            ->first();
        if ($newsletter) {
            $orderedCmsData->put('newsletter', $newsletter);
        }

        return $this->success(
            'Artist spotlight page CMS content retrieved successfully.',
            CmsContentResource::collection($orderedCmsData)->resolve()
        );
    }
}
