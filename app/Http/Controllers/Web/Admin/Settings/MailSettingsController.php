<?php

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MailSettingsController extends Controller
{
    use AdminApiResponse;

    /*
    |--------------------------------------------------------------------------
    | View mail settings page
    |--------------------------------------------------------------------------
    */
    public function mail(): View
    {
        return view('pages.admin.settings.mail',['s' => Setting::first()]);
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/mail
    |--------------------------------------------------------------------------
    */
    public function updateMail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mail_from_name'    => ['nullable', 'string', 'max:100'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_signature'    => ['nullable', 'string'],
        ], [
            'mail_from_address.email' => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        Setting::first()->update($request->only([
            'mail_from_name',
            'mail_from_address',
            'mail_signature',
        ]));

        return $this->success('Mail settings updated successfully.');
    }
}
