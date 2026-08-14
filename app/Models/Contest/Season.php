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
     *
     * Users can apply BEFORE the season starts. Once the season's start date
     * (starts_at) has arrived, applications are closed.
     *
     * If an explicit application window (applications_starts_at /
     * applications_ends_at) is configured, it is also respected.
     */
    public function canApply(): bool
    {
        // Must be active and have a defined start date
        if (!$this->is_active || !$this->starts_at) {
            return false;
        }

        $now = now();

        // Applications close once the season has started or application window closed
        $appEndsAt = $this->applications_ends_at ?? $this->starts_at;
        if ($now->gte($this->starts_at) || $now->gt($appEndsAt)) {
            return false;
        }

        // If status is 'open', accept applications immediately.
        // Otherwise, if applications_starts_at is configured in the future, respect it.
        if ($this->status !== 'open' && $this->applications_starts_at && $now->lt($this->applications_starts_at)) {
            return false;
        }

        return true;
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
     * Auto-generate slug and auto-sync application dates when saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Season $season) {
            if (empty($season->slug)) {
                $season->slug = Str::slug($season->title);
            }
        });

        static::saving(function (Season $season) {
            if ($season->starts_at) {
                // If starts_at changed or applications dates are empty, auto-sync them:
                // applications_ends_at defaults to starts_at
                if (empty($season->applications_ends_at) || $season->isDirty('starts_at')) {
                    $season->applications_ends_at = $season->starts_at;
                }

                // applications_starts_at defaults to 14 days before starts_at
                if (empty($season->applications_starts_at) || $season->isDirty('starts_at')) {
                    $season->applications_starts_at = $season->starts_at->copy()->subDays(14);
                }
            }
        });
    }
}
