<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Models\CMS;
use App\Enums\CmsPage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsContentController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $page = $request->query('page', CmsPage::HOME->value);
        $cmsData = CMS::where('page', $page)->where('status', 'active')->get();

        return $this->success('CMS content retrieved.', $cmsData);
    }
}
