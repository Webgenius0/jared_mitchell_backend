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

    public function updateVideo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'video_url' => ['nullable', 'url', 'max:500'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,mov,ogg,qt', 'max:20480'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::EVENTS,
            'section' => CmsSection::EVENTS_PAGE_VIDEO,
        ]);

        if ($request->hasFile('video_file')) {
            if ($cms->video && Str::startsWith($cms->video, 'uploads/')) {
                FileHandle::fileDelete($cms->video);
            }
            $cms->video = FileHandle::fileUpload($request->file('video_file'), 'cms/events/videos');
        } elseif ($request->filled('video_url')) {
            if ($cms->video && Str::startsWith($cms->video, 'uploads/')) {
                FileHandle::fileDelete($cms->video);
            }
            $cms->video = $request->video_url;
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

    /**
     * Update Vendor With OSI section
     */
    public function updateVendor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
            'pricing' => ['nullable', 'array'],
            'benefits' => ['nullable', 'array'],
            'member_perks_top' => ['nullable', 'array'],
            'member_perks_bottom' => ['nullable', 'array'],
            'what_vendors_provide' => ['nullable', 'array'],
            'why_vendors_love' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::EVENTS,
                'section' => CmsSection::EVENTS_PAGE_VENDOR,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'metadata' => [
                    'pricing' => $request->pricing ?? [],
                    'benefits' => $request->benefits ?? [],
                    'member_perks_top' => $request->member_perks_top ?? [],
                    'member_perks_bottom' => $request->member_perks_bottom ?? [],
                    'what_vendors_provide' => $request->what_vendors_provide ?? [],
                    'why_vendors_love' => $request->why_vendors_love ?? [],
                ]
            ]
        );

        return $this->success('Vendor section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Booth Features section
     */
    public function updateBoothFeatures(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
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
            'section' => CmsSection::EVENTS_PAGE_BOOTH_FEATURES,
        ]);

        $cms->title = $request->title;
        $cms->metadata = $request->items ?? [];
        $cms->save();

        return $this->success('Booth features updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Upcoming Event 1 section
     */
    public function updateUpcomingEvent1(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::EVENTS,
            'section' => CmsSection::EVENTS_PAGE_UPCOMING_EVENT1,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->save();

        return $this->success('Upcoming Event 1 section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Upcoming Event 2 section
     */
    public function updateUpcomingEvent2(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::EVENTS,
            'section' => CmsSection::EVENTS_PAGE_UPCOMING_EVENT2,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->save();

        return $this->success('Upcoming Event 2 section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Event Gallery section
     */
    public function updateEventGallery(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::EVENTS,
            'section' => CmsSection::EVENTS_PAGE_EVENT_GALLERY,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->save();

        return $this->success('Event Gallery section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Past Event Highlights section
     */
    public function updatePastEventHighlights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::EVENTS,
            'section' => CmsSection::EVENTS_PAGE_PAST_EVENT_HIGHLIGHTS,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->save();

        return $this->success('Past Event Highlights section updated successfully.', ['cms' => $cms]);
    }
}
