<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'business_category_id',
        'owner_name',
        'business_name',
        'slug',
        'year_founded',
        'website',
        'city',
        'state',
        'description',
        'logo',
        'status',
        'is_featured',
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
}
