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

class BossBeginningsCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Display Boss Beginnings CMS Page
     */
    public function index(Request $request): View
    {
        $cmsData = CMS::where('page', CmsPage::BOSS_BEGINNINGS)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof CmsSection ? $item->section->value : $item->section;
            });

        return view('web.admin.cms.content.index', [
            'cmsData' => $cmsData,
            'pages' => CmsPage::cases(),
            'currentPage' => CmsPage::BOSS_BEGINNINGS->value,
        ]);
    }

    /**
     * Update Hero Section
     */
    public function updateHero(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => ['nullable', 'string', 'max:500'],
            'sub_title'   => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:3000'],
            'bg_image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page'    => CmsPage::BOSS_BEGINNINGS,
            'section' => CmsSection::BOSS_BEGINNINGS_HERO,
        ]);

        $cms->title       = $request->title;
        $cms->sub_title   = $request->sub_title;
        $cms->description = $request->description;

        if ($request->hasFile('bg_image')) {
            if ($cms->image && (Str::startsWith($cms->image, 'uploads/') || file_exists(public_path($cms->image)))) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('bg_image'), 'cms/boss-beginnings');
        }

        $cms->save();

        return $this->success('Hero section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Features Section
     */
    public function updateFeatures(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'                  => ['nullable', 'string', 'max:500'],
            'description'            => ['nullable', 'string', 'max:2000'],
            'features'               => ['nullable', 'array'],
            'features.*.title'       => ['nullable', 'string', 'max:255'],
            'features.*.description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page'    => CmsPage::BOSS_BEGINNINGS,
            'section' => CmsSection::BOSS_BEGINNINGS_FEATURES,
        ]);

        $cms->title       = $request->title;
        $cms->description = $request->description;

        $features = $request->features ?? [];
        $existingFeatures = $cms->metadata['features'] ?? [];


        foreach ($features as $key => $item) {
            if ($request->hasFile("features.$key.image")) {

                if (isset($existingFeatures[$key]['image']) && !empty($existingFeatures[$key]['image'])) {
                    FileHandle::fileDelete($existingFeatures[$key]['image']);
                }
                $features[$key]['image'] = FileHandle::fileUpload($request->file("features.$key.image"), 'cms/boss-beginnings');
            } else {

                $features[$key]['image'] = $existingFeatures[$key]['image'] ?? null;
            }
        }

        $cms->metadata = ['features' => array_values($features)];
        $cms->save();

        return $this->success('Features section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Video & Gallery Section
     */
    public function updateVideoGallery(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'            => ['nullable', 'string', 'max:500'],
            'sub_title'        => ['nullable', 'string', 'max:1000'],
            'video_file'       => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:20480'],
            'gallery_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page'    => CmsPage::BOSS_BEGINNINGS,
            'section' => CmsSection::BOSS_BEGINNINGS_VIDEO_GALLERY,
        ]);

        $cms->title     = $request->title;
        $cms->sub_title = $request->sub_title;

        // Video Upload
        if ($request->hasFile('video_file')) {
            if ($cms->video && !empty($cms->video)) {
                FileHandle::fileDelete($cms->video);
            }
            $cms->video = FileHandle::fileUpload($request->file('video_file'), 'cms/boss-beginnings');
        }

        // Gallery Images Handling
        $existing = $cms->metadata['gallery'] ?? [];
        $remaining = $request->input('existing_gallery', []);

        // Delete images that were removed by the user
        $deleted = array_diff($existing, $remaining);
        foreach ($deleted as $img) {
            if (!empty($img)) {
                FileHandle::fileDelete($img);
            }
        }

        $gallery = $remaining;

        // Upload new images and append them
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $gallery[] = FileHandle::fileUpload($image, 'cms/boss-beginnings');
            }
        }

        $currentMetadata = is_array($cms->metadata) ? $cms->metadata : [];
        $cms->metadata = array_merge($currentMetadata, ['gallery' => $gallery]);

        $cms->save();

        return $this->success('Video & Gallery section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Steps Section
     */
    public function updateSteps(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'               => ['nullable', 'string', 'max:500'],
            'sub_title'           => ['nullable', 'string', 'max:1000'],
            'steps'               => ['nullable', 'array'],
            'steps.*.small_text'  => ['nullable', 'string', 'max:500'],
            'steps.*.title'       => ['nullable', 'string', 'max:255'],
            'steps.*.description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page'    => CmsPage::BOSS_BEGINNINGS,
            'section' => CmsSection::BOSS_BEGINNINGS_STEPS,
        ]);

        $cms->title     = $request->title;
        $cms->sub_title = $request->sub_title;

        $steps = $request->steps ?? [];
        $existingSteps = $cms->metadata['steps'] ?? [];


        foreach ($steps as $key => $item) {
            if ($request->hasFile("steps.$key.image")) {
                if (isset($existingSteps[$key]['image']) && !empty($existingSteps[$key]['image'])) {
                    FileHandle::fileDelete($existingSteps[$key]['image']);
                }
                $steps[$key]['image'] = FileHandle::fileUpload($request->file("steps.$key.image"), 'cms/boss-beginnings');
            } else {
                $steps[$key]['image'] = $existingSteps[$key]['image'] ?? null;
            }
        }

        $cms->metadata = ['steps' => array_values($steps)];
        $cms->save();

        return $this->success('Steps section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Section 5
     */
    public function updateSection5(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page'    => CmsPage::BOSS_BEGINNINGS,
            'section' => CmsSection::BOSS_BEGINNINGS_SECTION5,
        ]);

        $cms->title       = $request->title;
        $cms->description = $request->description;
        $cms->save();

        return $this->success('Section 5 updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Dynamic Items Section
     */
    public function updateDynamicSection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'               => ['nullable', 'string', 'max:500'],
            'description'         => ['nullable', 'string', 'max:2000'],
            'items'               => ['nullable', 'array'],
            'items.*.title'       => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page'    => CmsPage::BOSS_BEGINNINGS,
            'section' => CmsSection::BOSS_BEGINNINGS_DYNAMIC,
        ]);

        $cms->title       = $request->title;
        $cms->description = $request->description;

        $items = $request->items ?? [];
        $existingItems = $cms->metadata['items'] ?? [];


        foreach ($items as $key => $item) {
            if ($request->hasFile("items.$key.image")) {
                if (isset($existingItems[$key]['image']) && !empty($existingItems[$key]['image'])) {
                    FileHandle::fileDelete($existingItems[$key]['image']);
                }
                $items[$key]['image'] = FileHandle::fileUpload($request->file("items.$key.image"), 'cms/boss-beginnings');
            } else {
                $items[$key]['image'] = $existingItems[$key]['image'] ?? null;
            }
        }

        $cms->metadata = ['items' => array_values($items)];
        $cms->save();

        return $this->success('Dynamic section updated successfully.', ['cms' => $cms]);
    }
}
