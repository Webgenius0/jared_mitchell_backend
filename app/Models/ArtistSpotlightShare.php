<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistSpotlightShare extends Model
{
    protected $fillable = ['user_id', 'artist_spotlight_id', 'platform'];

    public function artistSpotlight()
    {
        return $this->belongsTo(ArtistSpotlight::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
