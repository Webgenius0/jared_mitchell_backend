<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            if ($cms->image && \Illuminate\Support\Str::startsWith($cms->image, 'uploads/')) {
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
}
