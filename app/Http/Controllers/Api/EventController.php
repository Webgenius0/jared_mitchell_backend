<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Resources\EventRegistrationResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicketTier;
use App\Services\EventRegistrationService;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EventRegistrationService $registrationService
    ) {}

    /**
     * List attendees for a specific event.
     */
    public function attendees($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $attendees = EventRegistration::where('event_id', $event->id)
            ->where('status', 'confirmed')
            ->with('user.profile')
            ->latest()
            ->paginate(20);

        return $this->success(
            'Attendees retrieved successfully.',
            [
                'attendees' => AttendeeResource::collection($attendees),
                'pagination' => [
                    'current_page' => $attendees->currentPage(),
                    'per_page' => $attendees->perPage(),
                    'total' => $attendees->total(),
                    'last_page' => $attendees->lastPage(),
                ]
            ]
        );
    }

    /**
     * List events with grouping/filtering.
     */
    public function index(Request $request)
    {
        $query = Event::where('status', 'published')
            ->with(['ticketTiers' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->withCount(['likers', 'bookmarkers', 'shares']);

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('event_type', $request->type);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Upcoming / Past filter
        if ($request->has('time')) {
            $now = now();
            if ($request->time === 'upcoming') {
                $query->where('ends_at', '>=', $now);
            } elseif ($request->time === 'past') {
                $query->where('ends_at', '<', $now);
            }
        }

        $events = $query->latest('starts_at')->paginate($request->input('per_page', 12));

        return $this->success(
            'Events retrieved successfully.',
            [
                'events' => EventResource::collection($events),
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                    'last_page' => $events->lastPage(),
                ]
            ]
        );
    }

    /**
     * Show event details.
     */
    public function show($slug)
    {
        $event = Event::where('slug', $slug)
            ->where('status', 'published')
            ->with(['ticketTiers' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->withCount(['likers', 'bookmarkers', 'shares'])
            ->first();

        if (!$event) {
            return $this->error('Event not found.', null, 404);
        }

        return $this->success(
            'Event details retrieved successfully.',
            new EventResource($event)
        );
    }

    /**
     * Register for an event.
     */
    public function register(Request $request)
    {
        $rules = [
            'event_id' => 'required|exists:events,id',
            'ticket_tier_id' => 'required|exists:event_ticket_tiers,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'quantity' => 'required|integer|min:1|max:10',
        ];

        // If guest, require password for account creation
        if (!auth('api')->check()) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $userId = auth('api')->id(); // null if guest
            $result = $this->registrationService->initiate($request->all(), $userId);

            return $this->success(
                $result['message'],
                [
                    'checkout_url' => $result['checkout_url'],
                    'session_id' => $result['session_id'] ?? null,
                    'registration' => new EventRegistrationResource($result['registration']),
                    'token' => $result['token'] ?? null,
                ],
                201
            );
        } catch (RuntimeException $e) {
            return $this->error(null, $e->getMessage(), 400);
        } catch (Exception $e) {
            Log::error('Event registration failed: ' . $e->getMessage());
            return $this->error(null, 'Failed to process registration. Please try again.');
        }
    }

    /**
     * Get member's event registrations (Dashboard)
     */
    public function myRegistrations(Request $request)
    {
        $userId = auth('api')->id();

        $registrations = EventRegistration::where('user_id', $userId)
            ->with(['event', 'ticketTier'])
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->success('Registrations retrieved successfully.', [
            'registrations' => EventRegistrationResource::collection($registrations),
            'pagination' => [
                'current_page' => $registrations->currentPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
                'last_page' => $registrations->lastPage(),
            ]
        ]);
    }

    /**
     * Cancel a pending registration
     */
    public function cancelRegistration($id)
    {
        $registration = EventRegistration::where('user_id', auth('api')->id())
            ->findOrFail($id);

        if ($registration->status !== 'pending') {
            return $this->error(null, 'Only pending registrations can be cancelled.', 400);
        }

        $this->registrationService->cancel($registration->id, 'Cancelled by user');

        return $this->success('Registration cancelled successfully.', new EventRegistrationResource($registration->fresh()));
    }

    /**
     * Download PDF Ticket
     */
    public function downloadTicket($id)
    {
        $registration = EventRegistration::where('user_id', auth('api')->id())
            ->where('status', 'confirmed')
            ->with(['event', 'ticketTier'])
            ->findOrFail($id);

        // Generate QR Code (Base64 string for PDF embedding)
        $qrCodeData = json_encode([
            'booking_reference' => $registration->booking_reference,
            'email' => $registration->email,
        ]);

        $qrCode = base64_encode(QrCode::format('svg')->size(200)->generate($qrCodeData));

        $pdf = Pdf::loadView('tickets.event-ticket', compact('registration', 'qrCode'));

        return $pdf->download("ticket-{$registration->booking_reference}.pdf");
    }

    /**
     * POST /api/events/{id}/like
     */
    public function toggleLike($id): JsonResponse
    {
        $user = auth()->user();
        $event = Event::findOrFail($id);

        $exists = $user->likedEvents()->where('event_id', $id)->exists();

        if ($exists) {
            $user->likedEvents()->detach($id);
            $message = 'Event unliked successfully.';
            $liked = false;
        } else {
            $user->likedEvents()->attach($id);
            $message = 'Event liked successfully.';
            $liked = true;
        }

        return $this->success($message, ['is_liked' => $liked]);
    }

    /**
     * POST /api/events/{id}/bookmark
     */
    public function toggleBookmark($id): JsonResponse
    {
        $user = auth()->user();
        $event = Event::findOrFail($id);

        $exists = $user->bookmarkedEvents()->where('event_id', $id)->exists();

        if ($exists) {
            $user->bookmarkedEvents()->detach($id);
            $message = 'Event removed from bookmarks.';
            $bookmarked = false;
        } else {
            $user->bookmarkedEvents()->attach($id);
            $message = 'Event bookmarked successfully.';
            $bookmarked = true;
        }

        return $this->success($message, ['is_bookmarked' => $bookmarked]);
    }

    /**
     * POST /api/events/{id}/share
     */
    public function recordShare(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        $event = Event::findOrFail($id);

        $event->shares()->create([
            'user_id' => $user?->id,
            'platform' => $request->platform,
        ]);

        return $this->success('Event share recorded successfully.');
    }
}
