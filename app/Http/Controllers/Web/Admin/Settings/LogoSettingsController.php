<?php

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LogoSettingsController extends Controller
{
    use AdminApiResponse;

    /*
    |--------------------------------------------------------------------------
    | View logo and branding page
    |--------------------------------------------------------------------------
    */

    public function logo(): View
    {
        return view('pages.admin.settings.logo',        ['s' => Setting::first()]);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /admin/settings/logo  (file uploads — multipart)
    |--------------------------------------------------------------------------
    */
    public function updateLogo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'logo'        => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'logo_light'  => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'logo_dark'   => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'logo_sm'     => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon'     => ['nullable', 'file',  'mimes:png,ico,jpg,jpeg',       'max:512'],
            'og_image'    => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp',      'max:2048'],
            'logo_width'  => ['nullable', 'integer', 'min:1', 'max:1000'],
            'logo_height' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $setting    = Setting::first();
        $fileFields = ['logo', 'logo_light', 'logo_dark', 'logo_sm', 'favicon', 'og_image'];
        $data       = [];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file first
                FileHandle::fileDelete($setting->$field);
                // Upload new file
                $data[$field] = FileHandle::fileUpload($request->file($field), 'settings');
            }
        }

        // Non-file fields
        if ($request->filled('logo_width'))  $data['logo_width']  = $request->logo_width;
        if ($request->filled('logo_height')) $data['logo_height'] = $request->logo_height;

        if (!empty($data)) {
            $setting->update($data);
        }

        return $this->success('Logo settings updated successfully.');
    }
}
