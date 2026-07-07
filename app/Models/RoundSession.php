<?php

namespace App\Models;

use App\Models\Contest\Season;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BRIDGE MODEL — Phase 1 Transition
 *
 * This model exists to provide backward compatibility while the codebase
 * migrates from "RoundSession" to "Season".
 *
 * It extends the new Season model and points to the 'seasons' table,
 * so all existing code referencing RoundSession continues to work.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property string $status
 * @property \Carbon\Carbon|null $starts_at
 * @property \Carbon\Carbon|null $ends_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @deprecated Use App\Models\Contest\Season instead. This class will be removed in Phase 2.
 */
class RoundSession extends Season
{
    /**
     * The table associated with the model.
     * Points to the new 'seasons' table — all queries go there.
     *
     * @var string
     */
    protected $table = 'seasons';

    /**
     * Get the old-style relationship to ContestApplication.
     * This is kept for backward compatibility.
     *
     * NOTE: This relationship assumes the contest_applications table
     * has been migrated to use 'season_id' (Phase 1 migration 4).
     * Run all Phase 1 migrations together before relying on this.
     *
     * @deprecated Use ->applications() on the Season model.
     */
    public function contestApplications(): HasMany
    {
        return $this->hasMany(ContestApplication::class, 'season_id');
    }

    /**
     * Accessor to maintain the old 'is_active' semantics.
     * In the new model, 'is_active' maps to the raw column.
     * This can be overridden if needed for transition logic.
     */
    public function getIsActiveAttribute($value): bool
    {
        // During transition, consider a season active if it's open or in_progress
        if (in_array($this->status, ['open', 'in_progress'])) {
            return true;
        }

        return (bool) $value;
    }


}
