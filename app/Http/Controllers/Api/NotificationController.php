<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/notifications
     *
     * List the authenticated user's notifications with pagination.
     * Supports filtering by read/unread status.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth('api')->user();

            $query = $user->notifications();

            // Filter by read/unread
            $filter = $request->input('filter');
            if ($filter === 'unread') {
                $query = $user->unreadNotifications();
            } elseif ($filter === 'read') {
                $query = $user->notifications()->whereNotNull('read_at');
            }

            $perPage = min((int) $request->input('per_page', 20), 100);

            $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $notifications->getCollection()->transform(function ($notification) {
                return $this->formatNotification($notification);
            });

            return $this->success('Notifications retrieved successfully.', [
                'data' => $notifications->items(),
                'pagination' => [
                    'total'        => $notifications->total(),
                    'per_page'     => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page'    => $notifications->lastPage(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to retrieve notifications.');
        }
    }

    /**
     * GET /api/v1/notifications/unread-count
     *
     * Get the count of unread notifications for the authenticated user.
     */
    public function unreadCount(): JsonResponse
    {
        try {
            $user  = auth('api')->user();
            $count = $user->unreadNotifications()->count();

            return $this->success('Unread count retrieved.', [
                'unread_count' => $count,
            ]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to retrieve unread count.');
        }
    }

    /**
     * POST /api/v1/notifications/mark-as-read
     *
     * Mark specific notifications as read by their IDs.
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'required|string|size:36', // UUID length
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user = auth('api')->user();
            $updated = $user->notifications()
                ->whereIn('id', $request->input('ids'))
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return $this->success("{$updated} notification(s) marked as read.", [
                'marked_read' => $updated,
            ]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to mark notifications as read.');
        }
    }

    /**
     * POST /api/v1/notifications/mark-all-as-read
     *
     * Mark all of the authenticated user's notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user    = auth('api')->user();
            $updated = $user->unreadNotifications()->update(['read_at' => now()]);

            return $this->success('All notifications marked as read.', [
                'marked_read' => $updated,
            ]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to mark notifications as read.');
        }
    }

    /**
     * POST /api/v1/notifications/{id}/mark-read
     *
     * Mark a single notification as read.
     */
    public function markOneAsRead(string $id): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $notification = $user->notifications()
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return $this->notFound('Notification not found.');
            }

            if ($notification->read_at === null) {
                $notification->markAsRead();
            }

            return $this->success('Notification marked as read.', [
                'data' => $this->formatNotification($notification->fresh()),
            ]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to mark notification as read.');
        }
    }

    /**
     * Format a notification for the API response.
     */
    private function formatNotification(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id'         => $notification->id,
            'type'       => $data['type'] ?? class_basename($notification->type),
            'message'    => $data['message'] ?? '',
            'data'       => $data,
            'read_at'    => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
            'is_read'    => $notification->read_at !== null,
        ];
    }
}
