<?php

namespace App\Http\Controllers\Api\Common;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\CmsContentResource;
use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CommnDataPassController extends Controller
{
    use ApiResponse;

    // Get Event Sponsor
    public function getEventSponsors()
    {
        $cmsData = CMS::where('page', CmsPage::HOME)
            ->where('section', CmsSection::PARTNERS)
            ->get();

        return $this->success(
            'Event sponsors or community partner retrieved successfully.',
            CmsContentResource::collection($cmsData)->resolve()
        );
    }

    // Get newsletter title
    public function newsletterTitle()
    {
        $cmsData = CMS::select('title')->where('page', CmsPage::HOME)
            ->where('section', CmsSection::NEWSLETTER)
            ->first();

        return $this->success(
            'Newsletter title retrieved successfully.',
            [
                'newsletter_title' => $cmsData->title
            ]
        );
    }
}
