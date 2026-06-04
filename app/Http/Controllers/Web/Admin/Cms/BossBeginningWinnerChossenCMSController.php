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

class BossBeginningWinnerChossenCMSController extends Controller
{
    use AdminApiResponse;

    /**
     * Display the Winner Chosen CMS Page
     */
    public function index(Request $request): View
    {
        // এখানে সরাসরি Enum পাস করা হয়েছে এবং ব্লেডে যাতে কোনো টাইপ মিসম্যাচ না হয়, তার সমাধান করা হয়েছে
        $cmsData = CMS::where('page', CmsPage::BOSS_BEGINNINGS_WINNER_CHOSEN)->get();

        return view('web.admin.cms.content.index', [
            'cmsData'     => $cmsData,
            'pages'       => CmsPage::cases(),
            'currentPage' => CmsPage::BOSS_BEGINNINGS_WINNER_CHOSEN->value,
        ]);
    }

    /**
     * Update Section 1
     */
    public function updateSection1(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:3000'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        // মডেলের কাস্টিং রুল অনুযায়ী এখানে সঠিক Enum অবজেক্ট ব্যবহার করা হয়েছে
        $cms = CMS::updateOrCreate(
            [
                'page'    => CmsPage::BOSS_BEGINNINGS_WINNER_CHOSEN,
                'section' => CmsSection::BOSS_BEGINNINGS_WINNER_CHOSEN_SECTION1,
            ],
            [
                'title'       => $request->title,
                'description' => $request->description,
            ]
        );

        if ($request->hasFile('image')) {
            if ($cms->image && (Str::startsWith($cms->image, 'uploads/') || file_exists(public_path($cms->image)))) {
                FileHandle::fileDelete($cms->image);
            }
            $cms->image = FileHandle::fileUpload($request->file('image'), 'cms/winner-chosen');
            $cms->save();
        }

        return $this->success('Section 1 updated successfully.', ['cms' => $cms]);
    }

    /**
     * Update Section 2 (3 Repeated Items)
     */
    public function updateSection2(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items'                    => ['required', 'array', 'min:3', 'max:3'],
            'items.*.small_text'       => ['nullable', 'string', 'max:255'],
            'items.*.title'            => ['nullable', 'string', 'max:255'],
            'items.*.description'      => ['nullable', 'string', 'max:1000'],
            'items.*.icon_image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page'    => CmsPage::BOSS_BEGINNINGS_WINNER_CHOSEN,
                'section' => CmsSection::BOSS_BEGINNINGS_WINNER_CHOSEN_SECTION2,
            ],
            ['status' => 'active']
        );

        $items = $request->items ?? [];
        $metadata = $cms->metadata;
        $existingItems = is_array($metadata) && isset($metadata['items']) ? $metadata['items'] : [];

        foreach ($items as $key => $item) {
            if ($request->hasFile("items.$key.icon_image")) {
                if (isset($existingItems[$key]['icon_image']) && !empty($existingItems[$key]['icon_image'])) {
                    FileHandle::fileDelete($existingItems[$key]['icon_image']);
                }
                $items[$key]['icon_image'] = FileHandle::fileUpload($request->file("items.$key.icon_image"), 'cms/winner-chosen');
            } else {
                $items[$key]['icon_image'] = $existingItems[$key]['icon_image'] ?? null;
            }
        }

        $cms->metadata = ['items' => array_values($items)];
        $cms->save();

        return $this->success('Section 2 updated successfully.', ['cms' => $cms]);
    }
}
