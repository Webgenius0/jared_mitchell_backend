<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WinnerArticleMedia extends Model
{
    use HasFactory;

    protected $table = 'winner_article_media';

    protected $fillable = [
        'winner_article_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_type',
        'file_size',
    ];

    protected $appends = [
        'url',
    ];

    /**
     * Get the winner article this media belongs to.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(WinnerArticle::class, 'winner_article_id');
    }

    /**
     * Get full public URL for the file.
     */
    public function getUrlAttribute(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        $cleanPath = preg_replace('#^storage/#', '', $this->file_path);
        return asset('storage/' . $cleanPath);
    }
}
