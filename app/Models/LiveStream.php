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
        'status',
    ];
}
