<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    protected $fillable = [
        'title',
        'description',
        'channel_arn',
        'ingest_endpoint',
        'stream_key',
        'playback_url',
        'tag_type',
        'streamable_type',
        'streamable_id',
        'status',
        'vod_url',
    ];

    /**
     * Get the parent streamable model (Event, Artist, Business, etc.)
     */
    public function streamable()
    {
        return $this->morphTo();
    }
}
