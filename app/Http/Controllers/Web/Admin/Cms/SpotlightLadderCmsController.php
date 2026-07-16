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

class SpotlightLadderCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Index
     */
    public function index(Request $request): View
    {
        $cmsData = CMS::where('page', CmsPage::SPOTLIGHT_LADDER)->get()->keyBy(function ($item) {
            return $item->section instanceof CmsSection ? $item->section->value : $item->section;
        });

        return view('web.admin.cms.content.index', [
            'cmsData' => $cmsData,
            'pages' => CmsPage::cases(),
            'currentPage' => CmsPage::SPOTLIGHT_LADDER->value,
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
            'page' => CmsPage::SPOTLIGHT_LADDER,
            'section' => CmsSection::SPOTLIGHT_LADDER_HERO,
        ]);

        $cms->title = $request->title;
        $cms->sub_title = $request->sub_title;

        if ($request->hasFile('bg_image')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('bg_image'), 'cms/spotlight-ladder');
        }

        $cms->save();

        return $this->success('Spotlight ladder hero updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Details section (headings & descriptions)
     */
    public function updateDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.heading' => ['nullable', 'string', 'max:500'],
            'items.*.description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::SPOTLIGHT_LADDER,
            'section' => CmsSection::SPOTLIGHT_LADDER_DETAILS,
        ]);

        $cms->title = $request->title;

        $itemsData = [];
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $itemsData[] = [
                    'heading' => $item['heading'] ?? null,
                    'description' => $item['description'] ?? null,
                ];
            }
        }

        $cms->metadata = $itemsData;
        $cms->save();

        return $this->success('Spotlight ladder details updated successfully.', ['cms' => $cms]);
    }
}
