<?php

namespace App\Models;

use App\Models\Contest\Contestant;
use App\Models\Contest\Season;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WinnerArticle extends Model
{
    use HasFactory;

    protected $table = 'winner_articles';

    protected $fillable = [
        'type',
        'contestant_id',
        'spotlight_week_nominee_id',
        'season_id',
        'spotlight_week_id',
        'title',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Attached media items (images and videos).
     */
    public function media(): HasMany
    {
        return $this->hasMany(WinnerArticleMedia::class, 'winner_article_id');
    }

    /**
     * Contestant winner (Boss Beginning).
     */
    public function contestant(): BelongsTo
    {
        return $this->belongsTo(Contestant::class, 'contestant_id');
    }

    /**
     * Spotlight nominee winner (Spotlight).
     */
    public function nominee(): BelongsTo
    {
        return $this->belongsTo(SpotlightWeekNominee::class, 'spotlight_week_nominee_id');
    }

    /**
     * Season (Boss Beginning).
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    /**
     * Spotlight week.
     */
    public function spotlightWeek(): BelongsTo
    {
        return $this->belongsTo(SpotlightWeek::class, 'spotlight_week_id');
    }
}
