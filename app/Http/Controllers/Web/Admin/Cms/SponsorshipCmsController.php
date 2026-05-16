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

class SponsorshipCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Index
     */
    public function index(Request $request): View
    {
        $cmsData = CMS::where('page', CmsPage::SPONSORSHIP)->get()->keyBy(function ($item) {
            return $item->section instanceof CmsSection ? $item->section->value : $item->section;
        });

        return view('web.admin.cms.content.index', [
            'cmsData' => $cmsData,
            'pages' => CmsPage::cases(),
            'currentPage' => CmsPage::SPONSORSHIP->value,
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
            'page' => CmsPage::SPONSORSHIP,
            'section' => CmsSection::SPONSORSHIP_PAGE_HERO,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;

        if ($request->hasFile('bg_image')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('bg_image'), 'cms/sponsorship');
        }

        $cms->save();

        return $this->success('Sponsorship hero updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Video section
     */
    public function updateVideo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'video_url' => ['nullable', 'url'],
            'video_thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SPONSORSHIP,
            'section' => CmsSection::SPONSORSHIP_PAGE_VIDEO,
        ]);

        $cms->sub_title = $request->video_url;

        if ($request->hasFile('video_thumbnail')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('video_thumbnail'), 'cms/sponsorship');
        }

        $cms->save();

        return $this->success('Video section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Why section
     */
    public function updateWhy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'intro' => ['nullable', 'string', 'max:1000'],
            'supports' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'features.*.title' => ['nullable', 'string', 'max:255'],
            'features.*.description' => ['nullable', 'string', 'max:500'],
            'features.*.icon' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SPONSORSHIP,
            'section' => CmsSection::SPONSORSHIP_PAGE_WHY,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->intro;
        $cms->metadata = [
            'supports' => $request->supports ?? [],
            'features' => $request->features ?? [],
        ];
        $cms->save();

        return $this->success('Why section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Steps section
     */
    public function updateSteps(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.list' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SPONSORSHIP,
            'section' => CmsSection::SPONSORSHIP_PAGE_STEPS,
        ]);

        $cms->title = $request->title;
        $cms->metadata = $request->items ?? [];
        $cms->save();

        return $this->success('Steps section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Levels Header section
     */
    public function updateLevelsHeader(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SPONSORSHIP,
            'section' => CmsSection::SPONSORSHIP_PAGE_LEVELS_HEADER,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->save();

        return $this->success('Levels header updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Footer section
     */
    public function updateFooter(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SPONSORSHIP,
            'section' => CmsSection::SPONSORSHIP_PAGE_FOOTER,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->save();

        return $this->success('Footer section updated successfully.', ['cms' => $cms]);
    }
}
