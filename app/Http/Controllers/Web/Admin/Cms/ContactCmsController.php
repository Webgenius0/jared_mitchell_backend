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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ContactCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Display contact page cms content
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.cms.content.index', ['page' => CmsPage::CONTACT->value]);
    }

    /**
     * Update hero section
     */
    public function updateHero(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string'],
            'image_file' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::CONTACT,
            'section' => CmsSection::CONTACT_HERO,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;

        if ($request->hasFile('image_file')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('image_file'), 'cms/contact');
        }

        $cms->save();

        return $this->success('Hero section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update spotlight section
     */
    public function updateSpotlight(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.link' => ['nullable', 'string', 'max:255'],
            'items.*.image_file' => ['nullable', 'file', 'image', 'max:5120'],
            'items.*.existing_image' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::CONTACT,
            'section' => CmsSection::CONTACT_SPOTLIGHT,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;

        $itemsData = [];
        $existingMetadata = $cms->metadata ?? [];
        $existingImages = collect($existingMetadata)->pluck('image')->toArray();

        if ($request->has('items')) {
            foreach ($request->items as $index => $item) {
                $imagePath = $item['existing_image'] ?? null;

                if ($request->hasFile("items.$index.image_file")) {
                    if ($imagePath && Str::startsWith($imagePath, 'uploads/')) {
                        FileHandle::fileDelete($imagePath);
                    }
                    $imagePath = FileHandle::fileUpload($request->file("items.$index.image_file"), 'cms/contact');
                }

                $itemsData[] = [
                    'image' => $imagePath,
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                    'link' => $item['link'] ?? null,
                ];
            }
        }

        $newImages = collect($itemsData)->pluck('image')->toArray();
        foreach ($existingImages as $oldImg) {
            if ($oldImg && !in_array($oldImg, $newImages) && Str::startsWith($oldImg, 'uploads/')) {
                FileHandle::fileDelete($oldImg);
            }
        }

        $cms->metadata = $itemsData;
        $cms->save();

        return $this->success('Spotlight section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update opportunities section
     */
    public function updateOpportunities(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.link' => ['nullable', 'string', 'max:255'],
            'items.*.icon_file' => ['nullable', 'file', 'image', 'max:5120'],
            'items.*.existing_icon' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::CONTACT,
            'section' => CmsSection::CONTACT_OPPORTUNITIES,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;

        $itemsData = [];
        $existingMetadata = $cms->metadata ?? [];
        $existingIcons = collect($existingMetadata)->pluck('icon')->toArray();

        if ($request->has('items')) {
            foreach ($request->items as $index => $item) {
                $iconPath = $item['existing_icon'] ?? null;

                if ($request->hasFile("items.$index.icon_file")) {
                    if ($iconPath && Str::startsWith($iconPath, 'uploads/')) {
                        FileHandle::fileDelete($iconPath);
                    }
                    $iconPath = FileHandle::fileUpload($request->file("items.$index.icon_file"), 'cms/contact');
                }

                $itemsData[] = [
                    'icon' => $iconPath,
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                    'link' => $item['link'] ?? null,
                ];
            }
        }

        $newIcons = collect($itemsData)->pluck('icon')->toArray();
        foreach ($existingIcons as $oldIcon) {
            if ($oldIcon && !in_array($oldIcon, $newIcons) && Str::startsWith($oldIcon, 'uploads/')) {
                FileHandle::fileDelete($oldIcon);
            }
        }

        $cms->metadata = $itemsData;
        $cms->save();

        return $this->success('Opportunities section updated successfully.', ['cms' => $cms]);
    }
}
