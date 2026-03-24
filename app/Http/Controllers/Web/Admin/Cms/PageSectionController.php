<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Section;
use App\Models\SectionContent;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageSectionController extends Controller
{
    use AdminApiResponse;

    public function updatePage(Request $request, Page $page): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'regex:/^[a-z0-9-]+$/', 'unique:pages,slug,' . $page->id],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $payload = [];

        if ($request->has('name')) {
            $payload['name'] = (string) $request->string('name')->trim()->value();
        }

        if ($request->has('slug')) {
            $slug = (string) $request->string('slug')->trim()->value();
            $payload['slug'] = $slug !== '' ? $slug : Str::slug((string) ($payload['name'] ?? $page->name));
        }

        if ($request->has('meta_title')) {
            $payload['meta_title'] = $request->input('meta_title');
        }

        if ($request->has('meta_description')) {
            $payload['meta_description'] = $request->input('meta_description');
        }

        if ($request->has('is_published')) {
            $payload['is_published'] = $request->boolean('is_published');
        }

        if (empty($payload)) {
            return $this->error('No update payload provided.', [], 422);
        }

        $page->update($payload);

        $this->bustPageCache($page->slug);

        return $this->success('Page updated successfully.', [
            'page' => $page->fresh(),
        ]);
    }

    public function storePage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'regex:/^[a-z0-9-]+$/', 'unique:pages,slug'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $slug = $request->filled('slug')
            ? (string) $request->string('slug')->trim()->value()
            : Str::slug((string) $request->string('name')->trim()->value());

        if ($slug === '') {
            return $this->error('Slug cannot be empty.', ['slug' => ['Slug cannot be empty.']], 422);
        }

        if (Page::where('slug', $slug)->exists()) {
            return $this->error('Slug already exists.', ['slug' => ['Slug already exists.']], 422);
        }

        $page = Page::create([
            'name' => (string) $request->string('name')->trim()->value(),
            'slug' => $slug,
            'meta_title' => (string) $request->string('name')->trim()->value() . ' | ' . config('app.name'),
            'meta_description' => null,
            'is_published' => $request->boolean('is_published', true),
        ]);

        Cache::forget('api:cms:pages:index');

        return $this->success('Page created successfully.', [
            'page' => $page,
            'redirect' => route('admin.cms.pages.index', ['page' => $page->slug]),
        ]);
    }

    public function storeSection(Request $request, Page $page): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'label' => ['required', 'string', 'max:150'],
            'section_key' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_\-]+$/'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $baseKey = $request->filled('section_key')
            ? (string) $request->string('section_key')->trim()->value()
            : Str::slug((string) $request->string('label')->trim()->value(), '_');

        if ($baseKey === '') {
            return $this->error('Section key cannot be empty.', ['section_key' => ['Section key cannot be empty.']], 422);
        }

        $sectionKey = $baseKey;
        $suffix = 1;
        while ($page->sections()->where('section_key', $sectionKey)->exists()) {
            $suffix++;
            $sectionKey = $baseKey . '_' . $suffix;
        }

        $nextOrder = ((int) $page->sections()->max('order')) + 1;

        $section = $page->sections()->create([
            'section_key' => $sectionKey,
            'label' => (string) $request->string('label')->trim()->value(),
            'order' => $nextOrder,
            'is_visible' => $request->boolean('is_visible', true),
        ]);

        $this->bustPageCache($page->slug);

        return $this->success('Section created successfully.', [
            'section' => $section,
        ]);
    }

    public function index(Request $request): View
    {
        $this->ensureExamplePages();

        $pages = Page::orderBy('name')->get();
        $selectedSlug = (string) $request->query('page', 'home');

        $page = Page::where('slug', $selectedSlug)->first() ?? $pages->first();

        if ($page) {
            // $this->ensureExampleSections($page);
            $page->load(['sections.contents', 'sections.items']);
        }

        return view('web.admin.cms.pages.index', [
            'pages' => $pages,
            'page' => $page,
        ]);
    }

    public function updateSection(Request $request, Section $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'label' => ['nullable', 'string', 'max:150'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $payload = [];

        if ($request->has('label')) {
            $payload['label'] = $request->string('label')->trim()->value();
        }

        if ($request->has('is_visible')) {
            $payload['is_visible'] = $request->boolean('is_visible');
        }

        if (! empty($payload)) {
            $section->update($payload);
        }

        $this->bustPageCache($section->page->slug);

        return $this->success('Section updated successfully.', [
            'section' => $section->fresh(['contents', 'items']),
        ]);
    }

    public function updateContents(Request $request, Section $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'locale' => ['nullable', 'string', 'max:10'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.field_key' => ['required', 'string', 'max:100'],
            'fields.*.field_type' => ['required', 'in:text,image,video,richtext,url,boolean'],
            'fields.*.value' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $locale = $request->string('locale', 'en')->value();

        foreach ((array) $request->input('fields', []) as $field) {
            $section->contents()->updateOrCreate(
                [
                    'field_key' => $field['field_key'],
                    'locale' => $locale,
                ],
                [
                    'field_type' => $field['field_type'],
                    'value' => $field['value'] ?? null,
                ]
            );
        }

        $this->bustPageCache($section->page->slug);

        return $this->success('Section content saved.');
    }

    public function uploadMedia(Request $request, Section $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'media' => ['required', 'file', 'max:20480'],
            'field_key' => ['required', 'string', 'max:100'],
            'field_type' => ['required', 'in:image,video'],
            'locale' => ['nullable', 'string', 'max:10'],
        ], [
            'media.required' => 'Please select a file to upload.',
            'media.max' => 'Maximum upload size is 20MB.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $fieldType = (string) $request->string('field_type')->value();
        $file = $request->file('media');

        $allowed = [
            'image' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'video' => ['mp4', 'mov', 'webm', 'm4v', 'avi'],
        ];

        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (! in_array($extension, $allowed[$fieldType], true)) {
            return $this->error('Invalid file type for ' . $fieldType . '.', [
                'media' => ['Allowed ' . $fieldType . ' formats: ' . implode(', ', $allowed[$fieldType]) . '.'],
            ], 422);
        }

        $locale = $request->string('locale', 'en')->value();
        $fieldKey = (string) $request->string('field_key')->value();

        $content = $section->contents()->firstOrNew([
            'field_key' => $fieldKey,
            'locale' => $locale,
        ]);

        if ($content->exists && ! empty($content->value) && Str::startsWith((string) $content->value, 'uploads/')) {
            FileHandle::fileDelete((string) $content->value);
        }

        $folder = $fieldType === 'video' ? 'cms/videos' : 'cms/images';
        $path = FileHandle::fileUpload($file, $folder);

        if (! $path) {
            return $this->error('Upload failed. Please try again.', [], 500);
        }

        $content->field_type = $fieldType;
        $content->value = $path;
        $content->save();

        $this->bustPageCache($section->page->slug);

        return $this->success(ucfirst($fieldType) . ' uploaded successfully.', [
            'path' => $path,
            'url' => asset('storage/' . $path),
            'field_key' => $fieldKey,
            'field_type' => $fieldType,
        ]);
    }

    public function storeContentField(Request $request, Section $section): JsonResponse
    {
        $normalizedFieldKey = Str::of((string) $request->input('field_key', ''))
            ->trim()
            ->lower()
            ->replaceMatches('/\s+/', '_')
            ->value();

        $request->merge([
            'field_key' => $normalizedFieldKey,
        ]);

        $validator = Validator::make($request->all(), [
            'field_key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_\-]+$/'],
            'field_type' => ['required', 'in:text,image,video,richtext,url,boolean'],
            'value' => ['nullable', 'string'],
            'locale' => ['nullable', 'string', 'max:10'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $locale = $request->string('locale', 'en')->value();
        $fieldKey = (string) $request->string('field_key')->trim()->value();

        if ($section->contents()->where('field_key', $fieldKey)->where('locale', $locale)->exists()) {
            return $this->error('Field key already exists for this section and locale.', [
                'field_key' => ['Field key already exists for this section and locale.'],
            ], 422);
        }

        $content = $section->contents()->create([
            'field_key' => $fieldKey,
            'field_type' => (string) $request->string('field_type')->value(),
            'value' => $request->input('value'),
            'locale' => $locale,
        ]);

        $this->bustPageCache($section->page->slug);

        return $this->success('Section field added successfully.', [
            'content' => $content,
        ]);
    }

    public function destroyContentField(Section $section, SectionContent $content): JsonResponse
    {
        if ((int) $content->section_id !== (int) $section->id) {
            return $this->error('Invalid section content reference.', [], 422);
        }

        if (in_array($content->field_type, ['image', 'video'], true) && ! empty($content->value) && Str::startsWith((string) $content->value, 'uploads/')) {
            FileHandle::fileDelete((string) $content->value);
        }

        $content->delete();

        $this->bustPageCache($section->page->slug);

        return $this->success('Section field removed successfully.');
    }

    public function updateItems(Request $request, Section $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array'],
            'items.*.order' => ['nullable', 'integer', 'min:0'],
            'items.*.data' => ['required', 'array'],
            'items.*.data.image' => ['nullable', 'string', 'max:2048'],
            'items.*.data.image_file' => ['nullable', 'file', 'image', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $oldImagePaths = $section->items()
            ->get()
            ->map(fn ($item) => (string) data_get($item->data, 'image', ''))
            ->filter(fn ($path) => $path !== '' && Str::startsWith($path, 'uploads/'))
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($request, $section): void {
            $section->items()->delete();

            foreach ((array) $request->input('items', []) as $index => $item) {

                $data = (array) ($item['data'] ?? []);
                $uploadedFile = $request->file("items.$index.data.image_file");

                if ($uploadedFile) {
                    $path = FileHandle::fileUpload($uploadedFile, 'cms/section_items');
                    if ($path) {
                        $data['image'] = $path;
                    }
                }

                unset($data['image_file']);

                $section->items()->create([
                    'order' => $item['order'] ?? ($index + 1),
                    'data' => $data,
                ]);
            }
        });

        $newImagePaths = $section->items()
            ->get()
            ->map(fn ($item) => (string) data_get($item->data, 'image', ''))
            ->filter(fn ($path) => $path !== '' && Str::startsWith($path, 'uploads/'))
            ->unique()
            ->values()
            ->all();

        foreach (array_diff($oldImagePaths, $newImagePaths) as $obsoletePath) {
            FileHandle::fileDelete($obsoletePath);
        }

        $this->bustPageCache($section->page->slug);

        return $this->success('Section items saved.');
    }

    public function destroySection(Section $section): JsonResponse
    {
        if ($section->is_visible) {
            return $this->error('Only non-visible sections can be removed.', [], 422);
        }

        $slug = $section->page->slug;

        $this->cleanupSectionMedia($section);
        $section->delete();

        $this->bustPageCache($slug);

        return $this->success('Section removed successfully.');
    }

    public function destroyPage(Page $page): JsonResponse
    {
        if ($page->is_published) {
            return $this->error('Only non-visible (draft) pages can be removed.', [], 422);
        }

        $slug = $page->slug;
        $page->load(['sections.contents']);

        foreach ($page->sections as $section) {
            $this->cleanupSectionMedia($section);
        }

        $page->delete();

        $this->bustPageCache($slug);

        return $this->success('Page removed successfully.', [
            'redirect' => route('admin.cms.pages.index'),
        ]);
    }

    public function reorderSections(Request $request, Page $page): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sections' => ['required', 'array', 'min:1'],
            'sections.*' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $ids = (array) $request->input('sections', []);

        $belongsToPage = $page->sections()->whereIn('id', $ids)->pluck('id')->all();

        if (count($belongsToPage) !== count($ids)) {
            return $this->error('One or more sections are invalid for this page.', [], 422);
        }

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                Section::whereKey($id)->update(['order' => $index + 1]);
            }
        });

        $this->bustPageCache($page->slug);

        return $this->success('Section order updated.');
    }

    private function bustPageCache(string $slug): void
    {
        Cache::forget('api:cms:pages:index');
        Cache::forget('api:cms:page:' . $slug . ':en');
    }

    private function cleanupSectionMedia(Section $section): void
    {
        $section->loadMissing('contents');

        foreach ($section->contents as $content) {
            if (in_array($content->field_type, ['image', 'video'], true) && ! empty($content->value) && Str::startsWith((string) $content->value, 'uploads/')) {
                FileHandle::fileDelete((string) $content->value);
            }
        }
    }

    private function ensureExamplePages(): void
    {
        if (Page::query()->exists()) {
            return;
        }

        $defaults = [
            ['name' => 'Home', 'slug' => 'home'],
            ['name' => 'About', 'slug' => 'about'],
            ['name' => 'Services', 'slug' => 'services'],
            // ['name' => 'Contact', 'slug' => 'contact'],
        ];

        foreach ($defaults as $item) {
            Page::create([
                'name' => $item['name'],
                'slug' => $item['slug'],
                'meta_title' => $item['name'] . ' | ' . config('app.name'),
                'meta_description' => 'Edit this page SEO from CMS panel.',
                'is_published' => true,
            ]);
        }
    }

    private function ensureExampleSections(Page $page): void
    {
        $templates = [
            [
                'section_key' => 'hero',
                'label' => 'Hero Section',
                'order' => 1,
                'fields' => [
                    ['field_key' => 'headline', 'field_type' => 'text', 'value' => 'Build faster with Jared Mitchell'],
                    ['field_key' => 'subheading', 'field_type' => 'richtext', 'value' => 'A flexible block-based content structure for your website pages.'],
                    ['field_key' => 'cta_text', 'field_type' => 'text', 'value' => 'Get Started'],
                    ['field_key' => 'cta_url', 'field_type' => 'url', 'value' => '/contact'],
                ],
                'items' => [],
            ],
            // [
            //     'section_key' => 'features',
            //     'label' => 'Features Section',
            //     'order' => 2,
            //     'fields' => [
            //         ['field_key' => 'title', 'field_type' => 'text', 'value' => 'Core Features'],
            //         ['field_key' => 'description', 'field_type' => 'richtext', 'value' => 'Use repeatable cards below to manage your features list.'],
            //     ],
            //     'items' => [
            //         ['title' => 'Fast Setup', 'description' => 'Launch quickly with predefined sections.', 'url' => '#', 'image' => ''],
            //         ['title' => 'Flexible Content', 'description' => 'EAV fields and JSON items work together.', 'url' => '#', 'image' => ''],
            //         ['title' => 'Admin Friendly', 'description' => 'Accordion-based editing keeps things clean.', 'url' => '#', 'image' => ''],
            //     ],
            // ],
            // [
            //     'section_key' => 'testimonials',
            //     'label' => 'Testimonials Section',
            //     'order' => 3,
            //     'fields' => [
            //         ['field_key' => 'title', 'field_type' => 'text', 'value' => 'Trusted by Teams'],
            //     ],
            //     'items' => [
            //         ['name' => 'Nadia', 'quote' => 'We ship pages in half the time now.'],
            //         ['name' => 'Rehan', 'quote' => 'The section structure is clean and scalable.'],
            //     ],
            // ],
            // [
            //     'section_key' => 'cta_banner',
            //     'label' => 'CTA Banner',
            //     'order' => 4,
            //     'fields' => [
            //         ['field_key' => 'title', 'field_type' => 'text', 'value' => 'Need a custom build?'],
            //         ['field_key' => 'button_text', 'field_type' => 'text', 'value' => 'Talk to us'],
            //         ['field_key' => 'button_url', 'field_type' => 'url', 'value' => '/contact'],
            //     ],
            //     'items' => [],
            // ],
        ];

        foreach ($templates as $template) {
            $section = $page->sections()->firstOrCreate(
                ['section_key' => $template['section_key']],
                [
                    'label' => $template['label'],
                    'order' => $template['order'],
                    'is_visible' => true,
                ]
            );

            foreach ($template['fields'] as $field) {
                $section->contents()->firstOrCreate(
                    [
                        'field_key' => $field['field_key'],
                        'locale' => 'en',
                    ],
                    [
                        'field_type' => $field['field_type'],
                        'value' => $field['value'],
                    ]
                );
            }

            if (! empty($template['items']) && $section->items()->count() === 0) {
                foreach ($template['items'] as $index => $item) {
                    $section->items()->create([
                        'order' => $index + 1,
                        'data' => $item,
                    ]);
                }
            }
        }
    }
}
