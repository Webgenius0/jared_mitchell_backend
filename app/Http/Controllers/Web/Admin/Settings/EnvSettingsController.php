<?php

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class EnvSettingsController extends Controller
{
    use ApiResponse;

    /*
    |--------------------------------------------------------------------------
    | Page Views
    |--------------------------------------------------------------------------
    */
    public function stripe(): View
    {
        return view('web.admin.settings.stripe');
    }
    public function reverb(): View
    {
        return view('web.admin.settings.reverb');
    }
    public function aws(): View
    {
        return view('web.admin.settings.aws');
    }
    public function imap(): View
    {
        return view('web.admin.settings.imap');
    }
    public function ai(): View
    {
        return view('web.admin.settings.ai');
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/stripe
    |--------------------------------------------------------------------------
    */
    public function updateStripe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stripe_key'            => ['required', 'string', 'max:255'],
            'stripe_secret'         => ['required', 'string', 'max:255'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
            'stripe_success_url'    => ['nullable', 'url', 'max:500'],
            'stripe_cancel_url'     => ['nullable', 'url', 'max:500'],
        ], [
            'stripe_key.required'    => 'Stripe publishable key is required.',
            'stripe_secret.required' => 'Stripe secret key is required.',
        ]);

        if ($validator->fails()) return $this->validationError($validator);

        $this->writeEnv([
            'STRIPE_KEY'            => $request->stripe_key,
            'STRIPE_SECRET'         => $request->stripe_secret,
            'STRIPE_WEBHOOK_SECRET' => $request->stripe_webhook_secret ?? '',
            'STRIPE_SUCCESS_URL'    => $request->stripe_success_url ?? '',
            'STRIPE_CANCEL_URL'     => $request->stripe_cancel_url ?? '',
        ]);

        Artisan::call('config:clear');

        return $this->success('Stripe settings updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/reverb
    |--------------------------------------------------------------------------
    */
    public function updateReverb(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reverb_app_id'     => ['required', 'string', 'max:100'],
            'reverb_app_key'    => ['required', 'string', 'max:255'],
            'reverb_app_secret' => ['required', 'string', 'max:255'],
            'reverb_host'       => ['required', 'string', 'max:255'],
            'reverb_port'       => ['required', 'integer', 'min:1', 'max:65535'],
            'reverb_server_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'reverb_scheme'     => ['required', 'in:http,https'],
        ], [
            'reverb_app_id.required'     => 'Reverb App ID is required.',
            'reverb_app_key.required'    => 'Reverb App Key is required.',
            'reverb_app_secret.required' => 'Reverb App Secret is required.',
            'reverb_host.required'       => 'Reverb host is required.',
            'reverb_port.required'       => 'Reverb port is required.',
            'reverb_scheme.in'           => 'Scheme must be http or https.',
        ]);

        if ($validator->fails()) return $this->validationError($validator);

        $this->writeEnv([
            'REVERB_APP_ID'      => $request->reverb_app_id,
            'REVERB_APP_KEY'     => $request->reverb_app_key,
            'REVERB_APP_SECRET'  => $request->reverb_app_secret,
            'REVERB_HOST'        => $request->reverb_host,
            'REVERB_PORT'        => $request->reverb_port,
            'REVERB_SERVER_PORT' => $request->reverb_server_port,
            'REVERB_SCHEME'      => $request->reverb_scheme,
        ]);

        Artisan::call('config:clear');

        return $this->success('Reverb settings updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/aws
    |--------------------------------------------------------------------------
    */
    public function updateAws(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'aws_access_key_id'          => ['nullable', 'string', 'max:255'],
            'aws_secret_access_key'      => ['nullable', 'string', 'max:255'],
            'aws_default_region'         => ['required', 'string', 'max:30'],
            'aws_bucket'                 => ['nullable', 'string', 'max:100'],
            'aws_use_path_style_endpoint' => ['required', 'boolean'],
        ], [
            'aws_default_region.required' => 'AWS region is required.',
        ]);

        if ($validator->fails()) return $this->validationError($validator);

        $this->writeEnv([
            'AWS_ACCESS_KEY_ID'           => $request->aws_access_key_id ?? '',
            'AWS_SECRET_ACCESS_KEY'       => $request->aws_secret_access_key ?? '',
            'AWS_DEFAULT_REGION'          => $request->aws_default_region,
            'AWS_BUCKET'                  => $request->aws_bucket ?? '',
            'AWS_USE_PATH_STYLE_ENDPOINT' => $request->aws_use_path_style_endpoint ? 'true' : 'false',
        ]);

        Artisan::call('config:clear');

        return $this->success('AWS S3 settings updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/imap
    |--------------------------------------------------------------------------
    */
    public function updateImap(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'imap_host'            => ['required', 'string', 'max:255'],
            'imap_port'            => ['required', 'integer', 'min:1', 'max:65535'],
            'imap_protocol'        => ['required', 'in:imap,pop3,nntp'],
            'imap_encryption'      => ['required', 'in:ssl,tls,notls'],
            'imap_validate_cert'   => ['required', 'boolean'],
            'imap_username'        => ['required', 'email', 'max:255'],
            'imap_password'        => ['nullable', 'string', 'max:255'],
            'imap_default_account' => ['nullable', 'string', 'max:100'],
        ], [
            'imap_host.required'     => 'IMAP host is required.',
            'imap_protocol.in'       => 'Protocol must be imap, pop3, or nntp.',
            'imap_encryption.in'     => 'Encryption must be ssl, tls, or notls.',
            'imap_username.required' => 'IMAP username (email) is required.',
            'imap_username.email'    => 'IMAP username must be a valid email address.',
        ]);

        if ($validator->fails()) return $this->validationError($validator);

        $this->writeEnv([
            'IMAP_HOST'            => $request->imap_host,
            'IMAP_PORT'            => $request->imap_port,
            'IMAP_PROTOCOL'        => $request->imap_protocol,
            'IMAP_ENCRYPTION'      => $request->imap_encryption,
            'IMAP_VALIDATE_CERT'   => $request->imap_validate_cert ? 'true' : 'false',
            'IMAP_USERNAME'        => $request->imap_username,
            'IMAP_PASSWORD'        => $request->imap_password ?? '',
            'IMAP_DEFAULT_ACCOUNT' => $request->imap_default_account ?? 'default',
        ]);

        Artisan::call('config:clear');

        return $this->success('IMAP settings updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | POST /admin/settings/artisan
    | Run safe artisan commands from the UI
    |--------------------------------------------------------------------------
    */
    public function runArtisan(Request $request): JsonResponse
    {
        $allowed = [
            'cache:clear'       => ['label' => 'Cache Clear',        'danger' => false],
            'config:clear'      => ['label' => 'Config Clear',       'danger' => false],
            'config:cache'      => ['label' => 'Config Cache',       'danger' => false],
            'route:clear'       => ['label' => 'Route Clear',        'danger' => false],
            'route:cache'       => ['label' => 'Route Cache',        'danger' => false],
            'view:clear'        => ['label' => 'View Clear',         'danger' => false],
            'view:cache'        => ['label' => 'View Cache',         'danger' => false],
            'optimize'          => ['label' => 'Optimize',           'danger' => false],
            'optimize:clear'    => ['label' => 'Optimize Clear',     'danger' => false],
            'event:clear'       => ['label' => 'Event Clear',        'danger' => false],
            'queue:restart'     => ['label' => 'Queue Restart',      'danger' => false],
            'storage:link'      => ['label' => 'Storage Link',       'danger' => false],
            'migrate'           => ['label' => 'Migrate',            'danger' => true],
            'migrate:fresh'     => ['label' => 'Migrate Fresh',      'danger' => true],
            'migrate:rollback'  => ['label' => 'Migrate Rollback',   'danger' => true],
            'db:seed'           => ['label' => 'Database Seed',      'danger' => true],
        ];

        $command = $request->input('command');

        if (! array_key_exists($command, $allowed)) {
            return $this->error('This command is not allowed.', [], 403);
        }

        try {
            $exitCode = Artisan::call($command);
            $output   = trim(Artisan::output());

            return $this->success(
                ($allowed[$command]['label']) . ' completed successfully.',
                ['output' => $output ?: 'Command completed with no output.']
            );
        } catch (\Throwable $e) {
            return $this->error('Command failed: ' . $e->getMessage(), [], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/ai
    |--------------------------------------------------------------------------
    */
    public function updateAi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ai_provider'        => ['required', 'in:openai,anthropic,gemini'],
            'openai_api_key'     => ['nullable', 'string', 'max:255'],
            'openai_organization'=> ['nullable', 'string', 'max:255'],
            'anthropic_api_key'  => ['nullable', 'string', 'max:255'],
            'gemini_api_key'     => ['nullable', 'string', 'max:255'],
        ], [
            'ai_provider.required' => 'Please select an active AI provider.',
            'ai_provider.in'       => 'Invalid AI provider selected.',
        ]);

        if ($validator->fails()) return $this->validationError($validator);

        $this->writeEnv([
            'AI_PROVIDER'         => $request->ai_provider,
            'OPENAI_API_KEY'      => $request->openai_api_key ?? '',
            'OPENAI_ORGANIZATION' => $request->openai_organization ?? '',
            'ANTHROPIC_API_KEY'   => $request->anthropic_api_key ?? '',
            'GEMINI_API_KEY'      => $request->gemini_api_key ?? '',
        ]);

        Artisan::call('config:clear');

        return $this->success('AI platform settings updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Private — Write key=value pairs to .env
    |--------------------------------------------------------------------------
    */
    private function writeEnv(array $data): void
    {
        $envPath    = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // Wrap values containing spaces in quotes
            $formatted = str_contains((string) $value, ' ') ? '"' . $value . '"' : $value;

            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$formatted}", $envContent);
            } else {
                $envContent .= "\n{$key}={$formatted}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
