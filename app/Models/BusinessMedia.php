<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BusinessMedia extends Model
{
    protected $table = 'business_media';

    protected $fillable = [
        'business_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    /**
     * The business this media belongs to.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get a temporary signed URL to access this private media file (valid 60 min).
     */
    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'business.media.serve',
            now()->addMinutes(60),
            ['media' => $this->id]
        );
    }
}
