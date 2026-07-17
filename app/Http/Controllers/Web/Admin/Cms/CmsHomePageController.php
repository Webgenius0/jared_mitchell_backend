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
use Illuminate\Support\Str;
use Illuminate\View\View;

class CmsHomePageController extends Controller
{
    use AdminApiResponse;

    /**
     * Index
     */
    public function index(Request $request): View
    {
        $page = $request->query('page', CmsPage::HOME->value);
        $cmsData = CMS::where('page', $page)->get()->keyBy(function ($item) {
            return $item->section instanceof CmsSection ? $item->section->value : $item->section;
        });

        return view('web.admin.cms.content.index', [
            'cmsData' => $cmsData,
            'pages' => CmsPage::cases(),
            'currentPage' => $page,
        ]);
    }

    /**
     * Update hero section
     */
    public function updateHero(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,mov,webm,m4v,avi', 'max:51200'], // 50MB
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::HERO,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'description' => $request->description,
            ]
        );

        if ($request->hasFile('video_file')) {
            if ($cms->video && Str::startsWith($cms->video, 'uploads/')) {
                FileHandle::fileDelete($cms->video);
            }
            $path = FileHandle::fileUpload($request->file('video_file'), 'cms/videos');
            $cms->update(['video' => $path]);
        }

        return $this->success('Hero section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update partners section
     */
    public function updatePartners(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'partners' => ['nullable', 'array'],
            'partners.*.link' => ['nullable', 'string', 'max:255'],
            'partners.*.image_file' => ['nullable', 'file', 'image', 'max:2048'],
            'partners.*.existing_image' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::HOME,
            'section' => CmsSection::PARTNERS,
        ]);

        $cms->title = $request->title;

        $partnerData = [];
        $existingMetadata = $cms->metadata ?? [];
        $existingImages = collect($existingMetadata)->pluck('image')->toArray();

        if ($request->has('partners')) {
            foreach ($request->partners as $index => $partner) {
                $imagePath = $partner['existing_image'] ?? null;

                if ($request->hasFile("partners.$index.image_file")) {
                    if ($imagePath && Str::startsWith($imagePath, 'uploads/')) {
                        FileHandle::fileDelete($imagePath);
                    }
                    $imagePath = FileHandle::fileUpload($request->file("partners.$index.image_file"), 'cms/partners');
                }

                $partnerData[] = [
                    'image' => $imagePath,
                    'link' => $partner['link'] ?? null,
                ];
            }
        }

        $newImages = collect($partnerData)->pluck('image')->toArray();
        foreach ($existingImages as $oldImg) {
            if ($oldImg && !in_array($oldImg, $newImages) && Str::startsWith($oldImg, 'uploads/')) {
                FileHandle::fileDelete($oldImg);
            }
        }

        $cms->metadata = $partnerData;
        $cms->save();

        return $this->success('Partners section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update static banner section
     */
    public function updateStaticBanner(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'bg_file' => ['nullable', 'file', 'image', 'max:5120'], // 5MB
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::STATIC_BANNER,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        if ($request->hasFile('bg_file')) {
            if ($cms->bg && Str::startsWith($cms->bg, 'uploads/')) {
                FileHandle::fileDelete($cms->bg);
            }
            $path = FileHandle::fileUpload($request->file('bg_file'), 'cms/backgrounds');
            $cms->update(['bg' => $path]);
        }

        return $this->success('Static banner section updated successfully.', [
            'cms' => $cms,
        ]);
    }


    /**
     * Update why choose section
     */
    public function updateWhyChoose(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.sub_title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.image_file' => ['nullable', 'file', 'image', 'max:2048'],
            'items.*.existing_image' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::HOME,
            'section' => CmsSection::WHY_CHOOSE,
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
                    $imagePath = FileHandle::fileUpload($request->file("items.$index.image_file"), 'cms/why_choose');
                }

                $itemsData[] = [
                    'image' => $imagePath,
                    'title' => $item['title'] ?? null,
                    'sub_title' => $item['sub_title'] ?? null,
                    'description' => $item['description'] ?? null,
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

        return $this->success('Why Choose section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update core values section
     */
    public function updateCoreValues(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'bg_file' => ['nullable', 'file', 'image', 'max:5120'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.sub_title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.image_file' => ['nullable', 'file', 'image', 'mimes:png', 'max:2048'],
            'items.*.existing_image' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page' => CmsPage::HOME,
            'section' => CmsSection::CORE_VALUES,
        ]);

        $cms->title = $request->title;

        if ($request->hasFile('bg_file')) {
            if ($cms->bg && Str::startsWith($cms->bg, 'uploads/')) {
                FileHandle::fileDelete($cms->bg);
            }
            $path = FileHandle::fileUpload($request->file('bg_file'), 'cms/core_values');
            $cms->bg = $path;
        }

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
                    $imagePath = FileHandle::fileUpload($request->file("items.$index.image_file"), 'cms/core_values');
                }

                $itemsData[] = [
                    'image' => $imagePath,
                    'title' => $item['title'] ?? null,
                    'sub_title' => $item['sub_title'] ?? null,
                    'description' => $item['description'] ?? null,
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

        return $this->success('Core Values section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update what you get section
     */
    public function updateWhatYouGet(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.icon' => ['nullable', 'string', 'max:255'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::WHAT_YOU_GET,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'metadata' => $request->items ?? [],
            ]
        );

        return $this->success('What You Really Getting section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update boss beginnings section
     */
    public function updateBossBeginnings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_file' => ['nullable', 'file', 'image', 'max:5120'], // 5MB
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::BOSS_BEGINNINGS,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'description' => $request->description,
            ]
        );

        if ($request->hasFile('image_file')) {
            if ($cms->image && Str::startsWith($cms->image, 'uploads/')) {
                FileHandle::fileDelete($cms->image);
            }
            $path = FileHandle::fileUpload($request->file('image_file'), 'cms/boss_beginnings');
            $cms->update(['image' => $path]);
        }

        return $this->success('Boss Beginnings section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update boss beginning winners section
     */
    public function updateBossBeginningWinners(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::BOSS_BEGINNING_WINNERS,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        return $this->success('Boss Beginning Winners section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update next boss beginnings - westside beauty lounge section
     */
    public function updateNextBossBeginnings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::NEXT_BOSS_BEGINNINGS_WESTSIDE_BEAUTY_LOUNGE,
            ],
            [
                'title' => $request->title,
            ]
        );

        return $this->success('Next Boss Beginnings - Westside Beauty Lounge section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update upcoming events section
     */
    public function updateUpcomingEvents(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::UPCOMING_EVENTS,
            ],
            [
                'title' => $request->title,
            ]
        );

        return $this->success('Upcoming Events section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update past event highlights section
     */
    public function updatePastEventHighlights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::PAST_EVENT_HIGHLIGHTS,
            ],
            [
                'title' => $request->title,
            ]
        );

        return $this->success('Past Event Highlights section updated successfully.', [
            'cms' => $cms,
        ]);
    }


    public function updateEventSponsors(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'event_sponsors' => ['nullable', 'array'],
            'event_sponsors.*.link' => ['nullable', 'string', 'max:255'],
            'event_sponsors.*.image_file' => ['nullable', 'file', 'image', 'max:2048'],
            'event_sponsors.*.existing_image' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::EVENT_SPONSORS,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        $sponsorData = [];
        $existingMetadata = $cms->metadata ?? [];
        $existingImages = collect($existingMetadata)->pluck('image')->toArray();

        if ($request->has('event_sponsors')) {
            foreach ($request->event_sponsors as $index => $sponsor) {
                $imagePath = $sponsor['existing_image'] ?? null;

                if ($request->hasFile("event_sponsors.$index.image_file")) {
                    if ($imagePath && Str::startsWith($imagePath, 'uploads/')) {
                        FileHandle::fileDelete($imagePath);
                    }
                    $imagePath = FileHandle::fileUpload($request->file("event_sponsors.$index.image_file"), 'cms/event_sponsors');
                }

                $sponsorData[] = [
                    'image' => $imagePath,
                    'link' => $sponsor['link'] ?? null,
                ];
            }
        }

        $newImages = collect($sponsorData)->pluck('image')->toArray();
        foreach ($existingImages as $oldImg) {
            if ($oldImg && !in_array($oldImg, $newImages) && Str::startsWith($oldImg, 'uploads/')) {
                FileHandle::fileDelete($oldImg);
            }
        }

        $cms->metadata = $sponsorData;
        $cms->save();

        return $this->success('Event Sponsors section updated successfully.', [
            'cms' => $cms,
        ]);
    }


    /**
     * Update spotlight section
     */
    public function updateSpotlight(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::SPOTLIGHT,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        return $this->success('Spotlight section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update highlights section
     */
    public function updateHighlights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::HIGHLIGHTS,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        return $this->success('Highlights section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update events section
     */
    public function updateEvents(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'bg_file' => ['nullable', 'file', 'image', 'max:5120'], // 5MB
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::EVENTS,
            ],
            [
                'title' => $request->title,
                'description' => $request->description,
            ]
        );

        if ($request->hasFile('bg_file')) {
            if ($cms->bg && \Illuminate\Support\Str::startsWith($cms->bg, 'uploads/')) {
                FileHandle::fileDelete($cms->bg);
            }
            $path = FileHandle::fileUpload($request->file('bg_file'), 'cms/events');
            $cms->update(['bg' => $path]);
        }

        return $this->success('Events section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update shop section
     */
    public function updateShop(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::SHOP,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
            ]
        );

        return $this->success('Shop section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update CTA section
     */
    public function updateCta(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::CTA,
            ],
            [
                'title' => $request->title,
            ]
        );

        return $this->success('CTA section updated successfully.', [
            'cms' => $cms,
        ]);
    }

    /**
     * Update newsletter section
     */
    public function updateNewsletter(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::NEWSLETTER,
            ],
            [
                'title' => $request->title,
            ]
        );

        return $this->success('Newsletter section updated successfully.', [
            'cms' => $cms,
        ]);
    }
}
