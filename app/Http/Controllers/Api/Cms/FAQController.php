<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class FAQController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/cms/faq
     *
     * Returns all active FAQs.
     * Cached for 5 minutes.
     */
    public function index(): JsonResponse
    {
        $faqs = Cache::remember('api:cms:faq:index', 300, function () {
            return FAQ::where('status', 'active')
                ->orderBy('id', 'desc')
                ->get();
        });

        return $this->success(
            'FAQs retrieved successfully.',
            $faqs
        );
    }
}
