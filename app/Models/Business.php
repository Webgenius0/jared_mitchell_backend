<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'business_name',
        'owner_founder_name',
        'story',
        'mission',
        'website_social_media',
        'community_impact_statement',
        'revenue_stage',
        'why_they_deserve_to_compete',
        'photo_video',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    public function interactions()
    {
        return $this->hasMany(BusinessInteraction::class);
    }

    public function claps()
    {
        return $this->interactions()->where('action_type', 'clap');
    }

    public function getPhotoVideoUrlAttribute(): ?string
    {
        if (!$this->photo_video) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::url($this->photo_video);
    }
}
