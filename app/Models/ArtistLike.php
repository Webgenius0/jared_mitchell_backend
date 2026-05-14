<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistLike extends Model
{
    protected $fillable = ['user_id', 'artist_id'];
}
