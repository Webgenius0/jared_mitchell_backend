<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\PageResource;
use App\Models\Page;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CmsPageController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/pages
     *
     * Returns all published pages (slug, name, meta — no sections).
     * Cached for 5 minutes; invalidated when admin saves page via admin CMS.
     */
    public function index(): JsonResponse
    {
        $pages = Cache::remember('api:cms:pages:index', 300, function () {
            return Page::where('is_published', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'meta_title', 'meta_description']);
        });

        return $this->success(
            'Pages retrieved.',
            PageResource::collection($pages)->resolve()
        );
    }

    /**
     * GET /api/v1/cms/pages/{slug}
     *
     * Returns a single published page with all visible sections,
     * content fields (keyed by field_key), and repeatable items.
     *
     * Query params:
     *   ?locale=en  (default: en)
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $locale   = preg_replace('/[^a-z0-9_-]/i', '', (string) $request->query('locale', 'en'));
        $cacheKey = "api:cms:page:{$slug}:{$locale}";

        $page = Cache::remember($cacheKey, 300, function () use ($slug, $locale) {
            return Page::where('slug', $slug)
                ->where('is_published', true)
                ->with([
                    'sections' => fn ($q) => $q->where('is_visible', true)->orderBy('order'),
                    'sections.contents' => fn ($q) => $q->where('locale', $locale)
                        ->select(['id', 'section_id', 'field_key', 'field_type', 'value']),
                    'sections.items' => fn ($q) => $q->orderBy('order')
                        ->select(['id', 'section_id', 'order', 'data']),
                ])
                ->first(['id', 'name', 'slug', 'meta_title', 'meta_description']);
        });

        if (! $page) {
            return $this->error(null, 'Page not found.', 404);
        }

        return $this->success('Page retrieved.', (new PageResource($page))->resolve());
    }
}
