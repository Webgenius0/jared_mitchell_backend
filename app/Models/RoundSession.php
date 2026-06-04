<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RoundSession extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($session) {
            if (!$session->slug) {
                $session->slug = Str::slug($session->title);
            }
        });
    }
}
