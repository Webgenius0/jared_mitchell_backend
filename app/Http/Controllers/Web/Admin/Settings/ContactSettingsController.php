<?php

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ContactSettingsController extends Controller
{
    use AdminApiResponse;

    /*
    |--------------------------------------------------------------------------
    | View contact settings page
    |--------------------------------------------------------------------------
    */
    public function contact(): View
    {
        return view('pages.admin.settings.contact',     ['s' => Setting::first()]);
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/contact
    |--------------------------------------------------------------------------
    */
    public function updateContact(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_email'   => ['nullable', 'email', 'max:255'],
            'support_email'   => ['nullable', 'email', 'max:255'],
            'noreply_email'   => ['nullable', 'email', 'max:255'],
            'phone_primary'   => ['nullable', 'string', 'max:30'],
            'phone_secondary' => ['nullable', 'string', 'max:30'],
            'whatsapp'        => ['nullable', 'string', 'max:30'],
            'address'         => ['nullable', 'string', 'max:500'],
            'city'            => ['nullable', 'string', 'max:100'],
            'state'           => ['nullable', 'string', 'max:100'],
            'country'         => ['nullable', 'string', 'max:100'],
            'zip_code'        => ['nullable', 'string', 'max:20'],
            'map_embed'       => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        Setting::first()->update($request->only([
            'contact_email',
            'support_email',
            'noreply_email',
            'phone_primary',
            'phone_secondary',
            'whatsapp',
            'address',
            'city',
            'state',
            'country',
            'zip_code',
            'map_embed',
        ]));

        return $this->success('Contact settings updated successfully.');
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
