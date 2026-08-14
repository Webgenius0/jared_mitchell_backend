<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Always a single row — no mass assignment issues here
    |--------------------------------------------------------------------------
    */
    protected $guarded = ['id'];

    protected $casts = [
        'logo_width'                  => 'integer',
        'logo_height'                 => 'integer',
        'cookie_consent_enabled'      => 'boolean',
        'maintenance_mode'            => 'boolean',
        'registration_enabled'        => 'boolean',
        'email_verification_required' => 'boolean',
        'boss_beginnings_start_date'  => 'datetime',
        'spotlight_start_date'        => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Cache key
    |--------------------------------------------------------------------------
    */
    public const CACHE_KEY = 'app_settings';

    /*
    |--------------------------------------------------------------------------
    | Retrieve (cached) — use this everywhere in the app
    |
    |  Usage:  Setting::current()
    |          Setting::current()->app_name
    |--------------------------------------------------------------------------
    */
    public static function current(): static
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::firstOrFail();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Clear cache — call after any update
    |--------------------------------------------------------------------------
    */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /*
    |--------------------------------------------------------------------------
    | Grouped accessors — handy for passing to views
    |--------------------------------------------------------------------------
    */

    /** Core identity fields */
    public function general(): array
    {
        return [
            'app_name'    => $this->app_name,
            'app_tagline' => $this->app_tagline,
            'app_version' => $this->app_version,
            'author_name' => $this->author_name,
            'author_url'  => $this->author_url,
            'copyright'   => $this->copyright,
            'about'       => $this->about,
        ];
    }

    /** All logo/image paths */
    public function branding(): array
    {
        return [
            'logo'        => $this->logo       ? asset('storage/' . $this->logo)       : null,
            'logo_light'  => $this->logo_light ? asset('storage/' . $this->logo_light) : null,
            'logo_dark'   => $this->logo_dark  ? asset('storage/' . $this->logo_dark)  : null,
            'logo_sm'     => $this->logo_sm    ? asset('storage/' . $this->logo_sm)    : null,
            'favicon'     => $this->favicon    ? asset('storage/' . $this->favicon)    : null,
            'og_image'    => $this->og_image   ? asset('storage/' . $this->og_image)   : null,
            'thumbnail'   => $this->thumbnail  ? asset('storage/' . $this->thumbnail)  : null,
            'logo_width'  => $this->logo_width,
            'logo_height' => $this->logo_height,
        ];
    }

    /** Social media links — returns only non-null values */
    public function socials(): array
    {
        return array_filter([
            'facebook'  => $this->facebook,
            'twitter'   => $this->twitter,
            'instagram' => $this->instagram,
            'linkedin'  => $this->linkedin,
            'youtube'   => $this->youtube,
            'tiktok'    => $this->tiktok,
            'pinterest' => $this->pinterest,
            'github'    => $this->github,
            'telegram'  => $this->telegram,
            'discord'   => $this->discord,
        ]);
    }

    /** Allowed IPs for maintenance bypass */
    public function maintenanceAllowedIps(): array
    {
        if (! $this->maintenance_allowed_ips) {
            return [];
        }

        return array_map('trim', explode(',', $this->maintenance_allowed_ips));
    }
}
