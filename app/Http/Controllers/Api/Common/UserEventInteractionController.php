<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\EventShare;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserEventInteractionController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/common/user-event-interactions
     *
     * Show only the authenticated user's own event interaction details:
     *   - role
     *   - name, email, photo
     *   - bookmark count + bookmarked event details
     *   - share count + shared event details (with platform)
     *
     * Accessible by any authenticated user (all roles); each user only
     * ever sees their own information.
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user()->load('profile', 'bookmarkedEvents');

        $shares = EventShare::with('event')
            ->where('user_id', $user->id)
            ->get();

        return $this->success('My event interactions retrieved successfully.', [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->profile?->name ?? '',
                'email' => $user->email ?? '',
                'photo' => $user->profile?->avatar_url ?? asset('admin/default/user.jpg'),
                'role'  => $user->getRoleNames()->first() ?? null,
            ],
            'bookmark_count'    => $user->bookmarkedEvents->count(),
            'share_count'       => $shares->count(),
            'bookmarked_events' => $user->bookmarkedEvents
                ->map(fn ($event) => $this->eventDetails($event))
                ->values(),
            'shared_events'     => $shares
                ->map(function ($share) {
                    return array_merge(
                        $this->eventDetails($share->event),
                        ['platform' => $share->platform]
                    );
                })
                ->values(),
        ]);
    }

    /**
     * Format a subset of event details for the response.
     */
    private function eventDetails(?object $event): array
    {
        if (!$event) {
            return [
                'id'    => null,
                'title' => '—',
            ];
        }

        return [
            'id'              => $event->id,
            'title'           => $event->title ?? '—',
            'slug'            => $event->slug ?? null,
            'event_type'      => $event->event_type ?? null,
            'starts_at'       => $event->starts_at?->toISOString(),
            'ends_at'         => $event->ends_at?->toISOString(),
            'venue_name'      => $event->venue_name ?? null,
            'city'            => $event->city ?? null,
            'state'           => $event->state ?? null,
            'cover_image'     => $event->cover_image_path
                ? asset('/' . ltrim($event->cover_image_path, '/'))
                : null,
            'status'          => $event->status ?? null,
        ];
    }
}
