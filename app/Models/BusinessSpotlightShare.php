<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSpotlightShare extends Model
{
    protected $fillable = ['user_id', 'business_spotlight_id', 'platform'];

    public function businessSpotlight()
    {
        return $this->belongsTo(BusinessSpotlight::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
