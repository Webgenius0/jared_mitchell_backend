<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMedia extends Model
{
    protected $table = 'event_media';

    protected $fillable = [
        'event_id',
        'media_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    /**
     * The event this media belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
