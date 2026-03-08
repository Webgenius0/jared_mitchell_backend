<?php

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class GeneralSettingsController extends Controller
{
    use AdminApiResponse;

    /**
     * View general settings page
     */
    public function general(): View
    {
        return view('pages.admin.settings.general',['s' => Setting::first()]);
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH  /admin/settings/general
    |--------------------------------------------------------------------------
    */
    public function updateGeneral(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'app_name'    => ['required', 'string', 'max:100'],
            'app_tagline' => ['nullable', 'string', 'max:200'],
            'app_version' => ['nullable', 'string', 'max:20'],
            'author_name' => ['nullable', 'string', 'max:100'],
            'author_url'  => ['nullable', 'url', 'max:255'],
            'copyright'   => ['nullable', 'string', 'max:255'],
            'about'       => ['nullable', 'string', 'max:1000'],
        ], [
            'app_name.required' => 'Application name is required.',
            'author_url.url'    => 'Author URL must be a valid URL.',
        ]);

        if ($validator->fails()) return $this->validationError($validator);

        $this->updateSetting($request->only([
            'app_name',
            'app_tagline',
            'app_version',
            'author_name',
            'author_url',
            'copyright',
            'about',
        ]));

        return $this->success('General settings updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Private helper — update single settings row + bust cache
    |--------------------------------------------------------------------------
    */
    private function updateSetting(array $data): void
    {
        Setting::first()->update($data);
        Setting::clearCache();
    }
}
