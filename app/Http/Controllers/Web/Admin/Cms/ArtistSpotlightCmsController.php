<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\CMS;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ArtistSpotlightCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Show artist spotlight CMS index
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.cms.content.index', ['page' => CmsPage::ARTIST_SPOTLIGHT->value]);
    }

    /**
     * Update Artist Spotlight Hero section
     */
    public function updateHero(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:500'],
            'bg_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::ARTIST_SPOTLIGHT,
            'section' => CmsSection::ARTIST_SPOTLIGHT_HERO,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;

        if ($request->hasFile('bg_image')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('bg_image'), 'cms/artist-spotlight');
        }

        $cms->save();

        return $this->success('Artist spotlight hero updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Artist Spotlight Video section
     */
    public function updateVideo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:500'],
            'video_url' => ['nullable', 'url'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::ARTIST_SPOTLIGHT,
            'section' => CmsSection::ARTIST_SPOTLIGHT_VIDEO,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->description = $request->video_url;

        if ($request->hasFile('thumbnail')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('thumbnail'), 'cms/artist-spotlight');
        }

        $cms->save();

        return $this->success('Artist spotlight video updated successfully.', ['cms' => $cms]);
    }
    /**
     * Update Artist Spotlight List section
     */
    public function updateList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            ['page' => CmsPage::ARTIST_SPOTLIGHT, 'section' => CmsSection::ARTIST_SPOTLIGHT_LIST],
            ['title' => $request->title, 'sub_title' => $request->sub_title]
        );

        return $this->success('Artist spotlight list section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Artist Spotlight Highlights section
     */
    public function updateHighlights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            ['page' => CmsPage::ARTIST_SPOTLIGHT, 'section' => CmsSection::ARTIST_SPOTLIGHT_HIGHLIGHTS],
            ['title' => $request->title, 'sub_title' => $request->sub_title]
        );

        return $this->success('Artist spotlight highlights section updated successfully.', ['cms' => $cms]);
    }
}
