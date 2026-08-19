<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoChannel extends Model
{
    use HasFactory;

    protected $table = 'video_channels';

    protected $fillable = [
        'category',
        'video_path',
        'title',
        'description',
        'thumbnail_path',
        'order',
        'is_active',
    ];

    protected $appends = [
        'category_label',
        'video_url',
    ];

    public const CATEGORIES = [
        'boss_beginning'     => 'Boss Beginning Video',
        'business_spotlight' => 'Business Spotlight Video',
        'artist_spotlight'   => 'Artist Spotlight Video',
        'event_video'        => 'Event Video',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function getVideoUrlAttribute(): ?string
    {
        if (!$this->video_path) {
            return null;
        }

        if (filter_var($this->video_path, FILTER_VALIDATE_URL)) {
            return $this->video_path;
        }

        return asset($this->video_path);
    }
}
