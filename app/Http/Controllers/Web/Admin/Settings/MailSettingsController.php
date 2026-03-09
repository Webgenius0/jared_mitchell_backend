<?php

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Helpers\EnvHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
        return view('web.admin.settings.mail', ['s' => Setting::first()]);
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/mail
    | Updates both .env (SMTP credentials) and settings table (from name/address)
    |--------------------------------------------------------------------------
    */
    public function updateMail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mail_mailer'       => ['required', 'string', 'in:smtp,sendmail,mailgun,ses,postmark,log,array'],
            'mail_host'         => ['required', 'string', 'max:255'],
            'mail_port'         => ['required', 'integer', 'in:25,465,587,2525'],
            'mail_username'     => ['nullable', 'string', 'max:255'],
            'mail_password'     => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name'    => ['required', 'string', 'max:100'],
            'mail_encryption'   => ['nullable', 'string', 'in:tls,ssl,'],
        ], [
            'mail_mailer.in'       => 'Mailer must be one of: smtp, sendmail, mailgun, ses, postmark.',
            'mail_host.required'   => 'SMTP host is required.',
            'mail_port.required'   => 'SMTP port is required.',
            'mail_port.in'         => 'Port must be 25, 465, 587, or 2525.',
            'mail_from_address.required' => 'From email address is required.',
            'mail_from_address.email'    => 'Please enter a valid email address.',
            'mail_from_name.required'    => 'From name is required.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        // ── Update .env file ───────────────────────────────────────────────
        $envData = [
            'MAIL_MAILER'       => $request->mail_mailer,
            'MAIL_HOST'         => $request->mail_host,
            'MAIL_PORT'         => $request->mail_port,
            'MAIL_USERNAME'     => $request->mail_username ?? '',
            'MAIL_PASSWORD'     => $request->mail_password ?? '',
            'MAIL_ENCRYPTION'   => $request->mail_encryption ?? null,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            'MAIL_FROM_NAME'    => $request->mail_from_name,
        ];

        // update .env values
        foreach ($envData as $key => $value) {
            EnvHelper::set($key, $value);
        }

        // ── Update settings table ──────────────────────────────────────────
        Setting::first()->update([
            'mail_from_name'    => $request->mail_from_name,
            'mail_from_address' => $request->mail_from_address,
            'mail_signature'    => $request->mail_signature,
        ]);

        // Clear config cache so new values take effect immediately
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return $this->success('Mail settings updated successfully.');
    }
}
