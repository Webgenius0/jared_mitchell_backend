<?php

namespace App\Services\Contest;

use App\Models\Business;
use App\Models\Contest\Contestant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class ContestNotificationService
{
    /**
     * Resolve the notifiable User for a contestant.
     *
     * Contestants have a polymorphic `contestable` relationship.
     * - If contestable is a User → notify directly.
     * - If contestable is a Business → notify the business owner.
     */
    public function resolveContestantUser(Contestant $contestant): ?User
    {
        $contestable = $contestant->contestable;

        if ($contestable instanceof User) {
            return $contestable;
        }

        if ($contestable instanceof Business) {
            // The business has a `user_id` or `owner_id` — find the owner
            $ownerId = $contestable->user_id ?? $contestable->owner_id ?? null;
            if ($ownerId) {
                return User::find($ownerId);
            }
        }

        return null;
    }

    /**
     * Resolve an array of contestant IDs to their notifiable Users.
     * Returns a collection of unique users.
     *
     * @param  array  $contestantData  Array of [ 'id' => int, ... ] from transition records
     * @return Collection<int, User>
     */
    public function resolveContestantUsers(array $contestantData): Collection
    {
        $ids = array_column($contestantData, 'id');
        $contestants = Contestant::whereIn('id', $ids)->get();
        $users = collect();

        foreach ($contestants as $contestant) {
            $user = $this->resolveContestantUser($contestant);
            if ($user) {
                $users->push($user);
            }
        }

        return $users->unique('id');
    }

    /**
     * Resolve the notifiable User from a contestable polymorphic reference.
     * Used by ApplicationSubmitted/ApplicationReviewed events.
     */
    public function resolveContestableUser(?string $contestableType, ?int $contestableId): ?User
    {
        if (!$contestableType || !$contestableId) {
            return null;
        }

        $contestable = app($contestableType)::find($contestableId);

        if ($contestable instanceof User) {
            return $contestable;
        }

        if ($contestable instanceof Business) {
            $ownerId = $contestable->user_id ?? $contestable->owner_id ?? null;
            if ($ownerId) {
                return User::find($ownerId);
            }
        }

        return null;
    }

    /**
     * Get all admin users who should receive contest notifications.
     */
    public function getAdminUsers(): Collection
    {
        $adminRoles = ['super-admin', 'admin'];

        return User::role($adminRoles)->get();
    }

    /**
     * Notify multiple users with a given notification instance.
     */
    public function notifyUsers(Collection $users, mixed $notification): void
    {
        foreach ($users as $user) {
            $user->notify($notification);
        }
    }
}
