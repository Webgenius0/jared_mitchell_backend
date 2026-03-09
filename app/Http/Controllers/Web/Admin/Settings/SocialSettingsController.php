<?php

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SocialSettingsController extends Controller
{
    use AdminApiResponse;

    /*
    |--------------------------------------------------------------------------
    | View social settings page
    |--------------------------------------------------------------------------
    */
    public function social(): View
    {
        return view('pages.admin.settings.social',      ['s' => Setting::first()]);
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH /admin/settings/social
    |--------------------------------------------------------------------------
    */
    public function updateSocial(Request $request): JsonResponse
    {
        $fields = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'github', 'telegram', 'discord'];

        $rules = [];
        foreach ($fields as $field) {
            $rules[$field] = ['nullable', 'url', 'max:255'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'facebook.url'  => 'Facebook must be a valid URL.',
            'twitter.url'   => 'Twitter must be a valid URL.',
            'instagram.url' => 'Instagram must be a valid URL.',
            'linkedin.url'  => 'LinkedIn must be a valid URL.',
            'youtube.url'   => 'YouTube must be a valid URL.',
            'tiktok.url'    => 'TikTok must be a valid URL.',
            'pinterest.url' => 'Pinterest must be a valid URL.',
            'github.url'    => 'GitHub must be a valid URL.',
            'telegram.url'  => 'Telegram must be a valid URL.',
            'discord.url'   => 'Discord must be a valid URL.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        Setting::first()->update($request->only($fields));

        return $this->success('Social media settings updated successfully.');
    }
}
