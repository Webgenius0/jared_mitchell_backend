<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([

            /* ── General ─────────────────────────────────────────────── */
            'app_name'        => config('app.name', 'My App'),
            'app_tagline'     => 'Build something great.',
            'app_version'     => '1.0.0',
            'author_name'     => 'Admin',
            'author_url'      => null,
            'copyright'       => '© ' . date('Y') . ' ' . config('app.name', 'My App') . '. All rights reserved.',
            'about'           => null,

            /* ── Branding ─────────────────────────────────────────────── */
            'logo'            => null,   // storage-relative path, e.g. "logos/logo.png"
            'logo_light'      => null,
            'logo_dark'       => null,
            'logo_sm'         => null,
            'logo_width'      => null,
            'logo_height'     => null,
            'favicon'         => null,
            'og_image'        => null,
            'thumbnail'       => null,

            /* ── Contact ─────────────────────────────────────────────── */
            'contact_email'   => 'contact@example.com',
            'support_email'   => 'support@example.com',
            'noreply_email'   => 'noreply@example.com',
            'phone_primary'   => null,
            'phone_secondary' => null,
            'whatsapp'        => null,
            'address'         => null,
            'city'            => null,
            'state'           => null,
            'country'         => null,
            'zip_code'        => null,
            'map_embed'       => null,

            /* ── Social Media ────────────────────────────────────────── */
            'facebook'        => null,
            'twitter'         => null,
            'instagram'       => null,
            'linkedin'        => null,
            'youtube'         => null,
            'tiktok'          => null,
            'pinterest'       => null,
            'github'          => null,
            'telegram'        => null,
            'discord'         => null,

            /* ── SEO / Meta ──────────────────────────────────────────── */
            'meta_title'                => config('app.name', 'My App'),
            'meta_description'          => null,
            'meta_keywords'             => null,
            'google_analytics_id'       => null,
            'google_tag_manager_id'     => null,
            'google_site_verification'  => null,
            'facebook_pixel_id'         => null,
            'header_scripts'            => null,
            'footer_scripts'            => null,

            /* ── Mail ────────────────────────────────────────────────── */
            'mail_from_name'    => config('app.name', 'My App'),
            'mail_from_address' => 'noreply@example.com',
            'mail_signature'    => null,

            /* ── Legal ───────────────────────────────────────────────── */
            'privacy_policy_url'     => null,
            'terms_url'              => null,
            'refund_policy_url'      => null,
            'cookie_consent_enabled' => false,
            'cookie_consent_message' => 'We use cookies to improve your experience.',

            /* ── System ──────────────────────────────────────────────── */
            'maintenance_mode'             => false,
            'maintenance_message'          => 'We are currently performing scheduled maintenance. Please check back soon.',
            'maintenance_allowed_ips'      => '127.0.0.1',
            'registration_enabled'         => true,
            'email_verification_required'  => false,
            'timezone'                     => 'UTC',
            'date_format'                  => 'd M Y',
            'time_format'                  => 'h:i A',
            'currency_code'                => 'USD',
            'currency_symbol'              => '$',
            'language'                     => 'en',

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
