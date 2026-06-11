<?php

namespace App\Models;

use App\Enums\CmsPage;
use App\Enums\CmsSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CMS extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'page' => CmsPage::class,
        'section' => CmsSection::class,
    ];

    public function getImageAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url(\Illuminate\Support\Str::startsWith($value, 'storage/') ? $value : Storage::url($value));
        }

        // Return only the path for web requests
        return $value;
    }

    public function getBgAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url(\Illuminate\Support\Str::startsWith($value, 'storage/') ? $value : Storage::url($value));
        }

        // Return only the path for web requests
        return $value;
    }
}
