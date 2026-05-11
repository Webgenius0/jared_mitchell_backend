<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Support\Str;

class BusinessSpotlightCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Index
     */
    public function index(Request $request): View
    {
        $cmsData = CMS::where('page', CmsPage::BUSINESS_SPOTLIGHT)->get()->keyBy(function ($item) {
            return $item->section instanceof CmsSection ? $item->section->value : $item->section;
        });

        return view('web.admin.cms.content.index', [
            'cmsData' => $cmsData,
            'pages' => CmsPage::cases(),
            'currentPage' => CmsPage::BUSINESS_SPOTLIGHT->value,
        ]);
    }

    /**
     * Update Hero section
     */
    public function updateHero(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
            'bg_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::BUSINESS_SPOTLIGHT,
            'section' => CmsSection::BUSINESS_SPOTLIGHT_HERO,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;

        if ($request->hasFile('bg_image')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('bg_image'), 'cms/business-spotlight');
        }

        $cms->save();

        return $this->success('Business spotlight hero updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Video section
     */
    public function updateVideo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:500'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::BUSINESS_SPOTLIGHT,
            'section' => CmsSection::BUSINESS_SPOTLIGHT_VIDEO,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->description = $request->video_url;

        if ($request->hasFile('thumbnail')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('thumbnail'), 'cms/business-spotlight');
        }

        $cms->save();

        return $this->success('Business spotlight video updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update List section
     */
    public function updateList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_LIST,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        return $this->success('Business list header updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Highlights section
     */
    public function updateHighlights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_HIGHLIGHTS,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        return $this->success('Business highlights updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Picks section
     */
    public function updatePicks(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_PICKS,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        return $this->success('Business picks updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Ladder section
     */
    public function updateLadder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_LADDER,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        return $this->success('Business ladder updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Join section
     */
    public function updateJoin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::BUSINESS_SPOTLIGHT,
                'section' => CmsSection::BUSINESS_SPOTLIGHT_JOIN,
            ],
            [
                'title' => $request->title,
            ]
        );

        return $this->success('Join section updated successfully.', ['cms' => $cms]);
    }
}
