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

class EventCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Index
     */
    public function index(Request $request): View
    {
        $cmsData = CMS::where('page', CmsPage::EVENTS)->get()->keyBy(function ($item) {
            return $item->section instanceof CmsSection ? $item->section->value : $item->section;
        });

        return view('web.admin.cms.content.index', [
            'cmsData' => $cmsData,
            'pages' => CmsPage::cases(),
            'currentPage' => CmsPage::EVENTS->value,
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
            'page' => CmsPage::EVENTS,
            'section' => CmsSection::EVENTS_PAGE_HERO,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;

        if ($request->hasFile('bg_image')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('bg_image'), 'cms/events');
        }

        $cms->save();

        return $this->success('Events hero updated successfully.', ['cms' => $cms]);
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
            'page' => CmsPage::EVENTS,
            'section' => CmsSection::EVENTS_PAGE_VIDEO,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->description = $request->video_url;

        if ($request->hasFile('thumbnail')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('thumbnail'), 'cms/events');
        }

        $cms->save();

        return $this->success('Events video updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Host Your Event section
     */
    public function updateHost(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.icon' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::EVENTS,
            'section' => CmsSection::EVENTS_PAGE_HOST,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;

        if ($request->hasFile('image_file')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('image_file'), 'cms/events');
        }

        $cms->metadata = $request->items ?? [];
        $cms->save();

        return $this->success('Host section updated successfully.', ['cms' => $cms]);
    }
}
