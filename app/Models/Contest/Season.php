<?php

namespace App\Models\Contest;

use App\Models\Contest\ContestApplication;
use App\Models\Contest\Contestant;
use App\Models\Round;
use App\Models\SeasonSponsor;
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Season extends Model
{
    use HasFactory;


    protected $table = 'seasons';

    protected $fillable = [
        'contest_type',
        'title',
        'slug',
        'description',
        'status',
        'configuration',
        'applications_starts_at',
        'applications_ends_at',
        'starts_at',
        'ends_at',
        'is_active',
        'is_featured',
        'metadata',
    ];

    protected $casts = [
        'configuration' => 'array',
        'metadata' => 'array',
        'applications_starts_at' => 'datetime',
        'applications_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * All rounds belonging to this season (ordered by round_number).
     */
    public function rounds()
    {
        return $this->hasMany(Round::class, 'season_id');
    }

    /**
     * All contestants participating in this season.
     */
    public function contestants()
    {
        return $this->hasMany(Contestant::class, 'season_id');
    }

    /**
     * All applications submitted to this season.
     */
    public function applications()
    {
        return $this->hasMany(ContestApplication::class, 'season_id');
    }

    /**
     * The sponsor assigned to this season (one sponsor per season).
     */
    public function sponsor()
    {
        return $this->hasOneThrough(
            Sponsor::class,
            SeasonSponsor::class,
            'season_id', // Foreign key on season_sponsor table
            'id',        // Foreign key on sponsors table
            'id',        // Local key on seasons table
            'sponsor_id' // Local key on season_sponsor table
        );
    }

    /**
     * Resolve route binding by id or slug.
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        if ($field !== null) {
            return static::where($field, $value)->first();
        }

        if (is_numeric($value)) {
            return static::find((int) $value);
        }

        return static::where('slug', $value)->first();
    }

    /**
     * Get the currently active season (open or in progress).
     */
    public static function active(): ?self
    {
        return static::query()
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereIn('status', ['open', 'in_progress']);
            })
            ->orderByDesc('id')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to only active seasons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by contest type (business, artist, startup, etc.).
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('contest_type', $type);
    }

    /**
     * Scope to seasons currently open for applications.
     */
    public function scopeOpenForApplications($query)
    {
        return $query->where('status', 'open')
            ->where('applications_starts_at', '<=', now())
            ->where('applications_ends_at', '>=', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the currently active round within this season.
     */
    public function currentRound(): ?Round
    {
        return $this->rounds()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('round_number')
            ->first();
    }

    /**
     * Whether this season is currently accepting applications.
     */
    public function canApply(): bool
    {
        return $this->is_active
            && $this->starts_at
            && $this->ends_at
            && now()->between($this->starts_at, $this->ends_at);
    }

    /**
     * Total number of contestants active in this season.
     */
    public function activeContestantsCount(): int
    {
        return $this->contestants()
            ->where('status', 'active')
            ->count();
    }

    /**
     * Auto-generate slug on creation if not provided.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Season $season) {
            if (empty($season->slug)) {
                $season->slug = Str::slug($season->title);
            }
        });
    }
}
