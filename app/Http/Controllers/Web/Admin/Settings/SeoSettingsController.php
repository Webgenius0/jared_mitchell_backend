<?php

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SeoSettingsController extends Controller
{
    use AdminApiResponse;

    /*
    |--------------------------------------------------------------------------
    | view stripe settings page
    |--------------------------------------------------------------------------
    */
    public function seo(): View
    {
        return view('web.admin.settings.seo', ['s' => Setting::first()]);
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/seo
    |--------------------------------------------------------------------------
    */
    public function updateSeo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'meta_title'               => ['nullable', 'string', 'max:160'],
            'meta_description'         => ['nullable', 'string', 'max:320'],
            'meta_keywords'            => ['nullable', 'string', 'max:500'],
            'google_analytics_id'      => ['nullable', 'string', 'max:30'],
            'google_tag_manager_id'    => ['nullable', 'string', 'max:20'],
            'google_site_verification' => ['nullable', 'string', 'max:100'],
            'facebook_pixel_id'        => ['nullable', 'string', 'max:30'],
            'header_scripts'           => ['nullable', 'string'],
            'footer_scripts'           => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        Setting::first()->update($request->only([
            'meta_title',
            'meta_description',
            'meta_keywords',
            'google_analytics_id',
            'google_tag_manager_id',
            'google_site_verification',
            'facebook_pixel_id',
            'header_scripts',
            'footer_scripts',
        ]));

        return $this->success('SEO settings updated successfully.');
    }
}
