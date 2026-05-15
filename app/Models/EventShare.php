<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventShare extends Model
{
    protected $fillable = ['user_id', 'event_id', 'platform'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
