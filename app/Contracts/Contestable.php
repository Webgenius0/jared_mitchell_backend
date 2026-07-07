<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Contract for entities that can participate in contests as contestants.
 *
 * Implement this on any model that should be eligible to compete:
 * - App\Models\Business (for Boss Beginnings)
 * - App\Models\User (for Artist Spotlight contests)
 * - Any future model (Startup, Team, etc.)
 */
interface Contestable
{
    /**
     * Get the display name shown on the leaderboard and contest pages.
     */
    public function getContestantName(): string;

    /**
     * Get the avatar URL shown on the leaderboard and contest cards.
     * Return null to use a default avatar.
     */
    public function getContestantAvatar(): ?string;

    /**
     * Define the polymorphic inverse relationship to contestants.
     * Each Contestable entity can be a contestant in multiple seasons.
     *
     * Implementation:
     *   return $this->morphMany(Contestant::class, 'contestable');
     */
    public function contestants(): MorphMany;
}
