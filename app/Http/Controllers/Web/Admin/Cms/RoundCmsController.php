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

class RoundCmsController extends Controller
{
    use AdminApiResponse;

    /**
     * Display the Rounds CMS Page.
     */
    public function index(Request $request): View
    {
        $cmsData = CMS::where('page', CmsPage::ROUNDS)
            ->get()
            ->keyBy(function ($item) {
                return $item->section instanceof CmsSection ? $item->section->value : $item->section;
            });

        return view('web.admin.cms.content.index', [
            'cmsData'     => $cmsData,
            'pages'       => CmsPage::cases(),
            'currentPage' => CmsPage::ROUNDS->value,
        ]);
    }

    /**
     * Update the Rounds CMS content at once.
     *
     * Structure stored in metadata:
     * {
     *   "block":  { title, subtitle, description, image (upload) },   // single top block
     *   "rounds": [                                                    // multiple rounds (max 5)
     *     { round_text, round_title, subtitle, icon (upload),
     *       goal_label, goal_text, requirements_label, requirements[] }
     *   ],
     *   "bottom": { title, subtitle, description }                    // single bottom section
     * }
     */
    public function updateRounds(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // ── Single top content block ──
            'block_title'       => ['nullable', 'string', 'max:255'],
            'block_subtitle'    => ['nullable', 'string', 'max:255'],
            'block_description' => ['nullable', 'string'],
            'block_image'       => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],

            // ── Multiple rounds (max 5) ──
            'rounds'                      => ['nullable', 'array', 'max:5'],
            'rounds.*.round_text'         => ['nullable', 'string', 'max:255'],
            'rounds.*.round_title'        => ['nullable', 'string', 'max:255'],
            'rounds.*.subtitle'           => ['nullable', 'string', 'max:255'],
            'rounds.*.icon'               => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'rounds.*.goal_label'         => ['nullable', 'string', 'max:255'],
            'rounds.*.goal_text'          => ['nullable', 'string', 'max:2000'],
            'rounds.*.requirements_label' => ['nullable', 'string', 'max:255'],
            'rounds.*.requirements'       => ['nullable', 'array'],
            'rounds.*.requirements.*'     => ['nullable', 'string', 'max:2000'],

            // ── Single bottom section ──
            'bottom_title'       => ['nullable', 'string', 'max:255'],
            'bottom_subtitle'    => ['nullable', 'string', 'max:255'],
            'bottom_description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::firstOrNew([
            'page'    => CmsPage::ROUNDS,
            'section' => CmsSection::ROUNDS,
        ]);

        $existingMetadata = is_array($cms->metadata) ? $cms->metadata : [];
        $existingBlock    = $existingMetadata['block'] ?? [];
        $existingRounds   = $existingMetadata['rounds'] ?? [];
        $existingBottom   = $existingMetadata['bottom'] ?? [];

        // Collect old image paths for cleanup
        $oldImages = [];
        if (!empty($existingBlock['image'])) {
            $oldImages[] = $existingBlock['image'];
        }
        foreach ($existingRounds as $er) {
            if (!empty($er['icon'])) {
                $oldImages[] = $er['icon'];
            }
        }

        // ── Single top content block ──
        $blockImage = $request->input('existing_block_image');

        if ($request->hasFile('block_image')) {
            if ($blockImage && Str::startsWith($blockImage, 'uploads/')) {
                FileHandle::fileDelete($blockImage);
            }
            $blockImage = FileHandle::fileUpload($request->file('block_image'), 'cms/rounds');
        }

        $block = [
            'title'       => $request->block_title,
            'subtitle'    => $request->block_subtitle,
            'description' => $request->block_description,
            'image'       => $blockImage,
        ];

        // ── Multiple rounds ──
        $rounds = [];

        if ($request->has('rounds')) {
            foreach ($request->rounds as $index => $round) {
                $iconPath = $round['existing_icon'] ?? null;

                if ($request->hasFile("rounds.$index.icon")) {
                    if ($iconPath && Str::startsWith($iconPath, 'uploads/')) {
                        FileHandle::fileDelete($iconPath);
                    }
                    $iconPath = FileHandle::fileUpload($request->file("rounds.$index.icon"), 'cms/rounds');
                }

                $rounds[] = [
                    'round_text'         => $round['round_text'] ?? null,
                    'round_title'        => $round['round_title'] ?? null,
                    'subtitle'           => $round['subtitle'] ?? null,
                    'icon'               => $iconPath,
                    'goal_label'         => $round['goal_label'] ?? null,
                    'goal_text'          => $round['goal_text'] ?? null,
                    'requirements_label' => $round['requirements_label'] ?? null,
                    'requirements'       => array_values(array_filter($round['requirements'] ?? [], fn ($r) => trim((string) $r) !== '')),
                ];
            }
        }

        // Delete old images that are no longer referenced
        $newImages = [];
        if (!empty($block['image'])) {
            $newImages[] = $block['image'];
        }
        foreach ($rounds as $round) {
            if (!empty($round['icon'])) {
                $newImages[] = $round['icon'];
            }
        }
        foreach ($oldImages as $oldImg) {
            if ($oldImg && !in_array($oldImg, $newImages) && Str::startsWith($oldImg, 'uploads/')) {
                FileHandle::fileDelete($oldImg);
            }
        }

        $cms->metadata = [
            'block'  => $block,
            'rounds' => array_values($rounds),
            'bottom' => [
                'title'       => $request->bottom_title ?? $existingBottom['title'] ?? null,
                'subtitle'    => $request->bottom_subtitle ?? $existingBottom['subtitle'] ?? null,
                'description' => $request->bottom_description ?? $existingBottom['description'] ?? null,
            ],
        ];
        $cms->save();

        return $this->success('Rounds updated successfully.', ['cms' => $cms]);
    }
}
