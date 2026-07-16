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
use Illuminate\View\View;

class ServiceCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Show services CMS index
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.cms.content.index', ['page' => CmsPage::SERVICES->value]);
    }

    /**
     * Update Services Hero section
     */
    public function updateHero(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'bg_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SERVICES,
            'section' => CmsSection::SERVICES_HERO,
        ]);

        $cms->title = $request->title;

        if ($request->hasFile('bg_image')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('bg_image'), 'cms/services');
        }

        $cms->save();

        return $this->success('Services hero updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Services Overview section
     */
    public function updateOverview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_OVERVIEW],
            ['description' => $request->description]
        );

        return $this->success('Services overview updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Services Grow section
     */
    public function updateGrow(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SERVICES,
            'section' => CmsSection::SERVICES_GROW,
        ]);

        $cms->title = $request->title;
        $cms->description = $request->description;

        if ($request->hasFile('image_file')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('image_file'), 'cms/services');
        }

        $cms->save();

        return $this->success('Services grow section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Services Partners section
     */
    public function updatePartners(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.link' => ['nullable', 'url'],
            'items.*.image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SERVICES,
            'section' => CmsSection::SERVICES_PARTNERS,
        ]);

        $cms->title = $request->title;
        $cms->description = $request->description;

        $items = $request->input('items', []);
        $formattedItems = [];

        foreach ($items as $index => $item) {
            $imagePath = $item['existing_image'] ?? '';

            if ($request->hasFile("items.{$index}.image_file")) {
                if ($imagePath && Str::startsWith($imagePath, 'uploads/')) {
                    FileHandle::fileDelete($imagePath);
                }
                $imagePath = FileHandle::fileUpload($request->file("items.{$index}.image_file"), 'cms/services');
            }

            $formattedItems[] = [
                'image' => $imagePath,
                'link' => $item['link'] ?? '',
            ];
        }

        $cms->metadata = $formattedItems;
        $cms->save();

        return $this->success('Services partners section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Who OSI Is For section
     */
    public function updateWhoFor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SERVICES,
            'section' => CmsSection::SERVICES_WHO_FOR,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;
        $cms->description = $request->description;

        $items = $request->input('items', []);
        $formattedItems = [];

        foreach ($items as $index => $item) {
            $imagePath = $item['existing_image'] ?? '';

            if ($request->hasFile("items.{$index}.image_file")) {
                if ($imagePath && Str::startsWith($imagePath, 'uploads/')) {
                    FileHandle::fileDelete($imagePath);
                }
                $imagePath = FileHandle::fileUpload($request->file("items.{$index}.image_file"), 'cms/services');
            }

            $formattedItems[] = [
                'title' => $item['title'] ?? '',
                'icon' => $item['icon'] ?? '',
                'image' => $imagePath,
            ];
        }

        $cms->metadata = $formattedItems;
        $cms->save();

        return $this->success('Who OSI Is For section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Artist Spotlight section
     */
    public function updateArtistSpotlight(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_ARTIST_SPOTLIGHT],
            ['title' => $request->title, 'sub_title' => $request->sub_title]
        );

        return $this->success('Artist spotlight section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Business Spotlight section
     */
    public function updateBusinessSpotlight(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_BUSINESS_SPOTLIGHT],
            ['title' => $request->title, 'sub_title' => $request->sub_title]
        );

        return $this->success('Business spotlight section updated successfully.', ['cms' => $cms]);
    }


    /**
     * Update Services Newsletter section
     */
    public function updateNewsletter(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_NEWSLETTER],
            ['title' => $request->title]
        );

        return $this->success('Services newsletter section updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Services Risk Free section
     */
    public function updateRiskFree(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:500'],
            'sub_title' => ['nullable', 'string', 'max:500'],
            'points' => ['nullable', 'array'],
            'points.*' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            ['page' => CmsPage::SERVICES, 'section' => CmsSection::SERVICES_RISK_FREE],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'metadata' => $request->points ?? []
            ]
        );

        return $this->success('Services risk free section updated successfully.', ['cms' => $cms]);
    }
}
