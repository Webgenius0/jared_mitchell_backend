<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\CMS;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AboutCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Display about page cms content
     */
    public function index(): View
    {
        $cmsData = CMS::where('page', CmsPage::ABOUT)->get()->keyBy(function ($item) {
            return $item->section instanceof \App\Enums\CmsSection ? $item->section->value : $item->section;
        });

        return view('web.admin.cms.about.index', [
            'cmsData' => $cmsData,
            'pages' => CmsPage::cases(),
            'currentPage' => CmsPage::ABOUT->value,
        ]);
    }

    /**
     * Update about hero section
     */
    public function updateHero(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string'],
            'bg_file' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::ABOUT,
                'section' => CmsSection::ABOUT_HERO,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        if ($request->hasFile('bg_file')) {
            if ($cms->bg && \Illuminate\Support\Str::startsWith($cms->bg, 'uploads/')) {
                FileHandle::fileDelete($cms->bg);
            }
            $path = FileHandle::fileUpload($request->file('bg_file'), 'cms/about');
            $cms->update(['bg' => $path]);
        }

        return $this->success('About hero updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update about society section
     */
    public function updateSociety(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_file' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::ABOUT,
                'section' => CmsSection::ABOUT_SOCIETY,
            ],
            [
                'title' => $request->title,
                'description' => $request->description,
            ]
        );

        if ($request->hasFile('image_file')) {
            if ($cms->image && \Illuminate\Support\Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $path = FileHandle::fileUpload($request->file('image_file'), 'cms/about');
            $cms->update(['image' => $path]);
        }

        return $this->success('Society section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update about origin story section
     */
    public function updateOrigin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_file' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::ABOUT,
                'section' => CmsSection::ABOUT_ORIGIN,
            ],
            [
                'title' => $request->title,
                'description' => $request->description,
            ]
        );

        if ($request->hasFile('image_file')) {
            if ($cms->image && \Illuminate\Support\Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $path = FileHandle::fileUpload($request->file('image_file'), 'cms/about');
            $cms->update(['image' => $path]);
        }

        return $this->success('Origin story updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update mission & purpose section
     */
    public function updateMission(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.image_file' => ['nullable', 'file', 'image', 'max:5120'],
            'items.*.existing_image' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::ABOUT,
            'section' => CmsSection::ABOUT_MISSION,
        ]);

        $cms->title = $request->title;

        $itemsData = [];
        $existingMetadata = $cms->metadata ?? [];
        $existingImages = collect($existingMetadata)->pluck('image')->toArray();

        if ($request->has('items')) {
            foreach ($request->items as $index => $item) {
                $imagePath = $item['existing_image'] ?? null;

                if ($request->hasFile("items.$index.image_file")) {
                    if ($imagePath && \Illuminate\Support\Str::startsWith($imagePath, 'uploads/')) {
                        FileHandle::fileDelete($imagePath);
                    }
                    $imagePath = FileHandle::fileUpload($request->file("items.$index.image_file"), 'cms/about');
                }

                $itemsData[] = [
                    'image' => $imagePath,
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                ];
            }
        }

        $newImages = collect($itemsData)->pluck('image')->toArray();
        foreach ($existingImages as $oldImg) {
            if ($oldImg && !in_array($oldImg, $newImages) && \Illuminate\Support\Str::startsWith($oldImg, 'uploads/')) {
                FileHandle::fileDelete($oldImg);
            }
        }

        $cms->metadata = $itemsData;
        $cms->save();

        return $this->success('Mission & Purpose section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update what we do section
     */
    public function updateWhatWeDo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.icon' => ['nullable', 'string', 'max:255'],
            'items.*.image_file' => ['nullable', 'file', 'image', 'max:5120'],
            'items.*.existing_image' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::ABOUT,
            'section' => CmsSection::ABOUT_WHAT_WE_DO,
        ]);

        $cms->title = $request->title;

        $itemsData = [];
        $existingMetadata = $cms->metadata ?? [];
        $existingImages = collect($existingMetadata)->pluck('image')->toArray();

        if ($request->has('items')) {
            foreach ($request->items as $index => $item) {
                $imagePath = $item['existing_image'] ?? null;

                if ($request->hasFile("items.$index.image_file")) {
                    if ($imagePath && \Illuminate\Support\Str::startsWith($imagePath, 'uploads/')) {
                        FileHandle::fileDelete($imagePath);
                    }
                    $imagePath = FileHandle::fileUpload($request->file("items.$index.image_file"), 'cms/about');
                }

                $itemsData[] = [
                    'image' => $imagePath,
                    'icon' => $item['icon'] ?? null,
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                ];
            }
        }

        $newImages = collect($itemsData)->pluck('image')->toArray();
        foreach ($existingImages as $oldImg) {
            if ($oldImg && !in_array($oldImg, $newImages) && \Illuminate\Support\Str::startsWith($oldImg, 'uploads/')) {
                FileHandle::fileDelete($oldImg);
            }
        }

        $cms->metadata = $itemsData;
        $cms->save();

        return $this->success('What We Do section updated successfully.', ['cms' => $cms]);
    }
}
