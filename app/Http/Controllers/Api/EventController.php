<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicketTier;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    use ApiResponse;

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
            }]);

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
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'ticket_tier_id' => 'required|exists:event_ticket_tiers,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $event = Event::find($request->event_id);
        if (!$event) {
            return $this->error('Event not found.', null, 404);
        }

        $tier = EventTicketTier::where('id', $request->ticket_tier_id)
            ->where('event_id', $event->id)
            ->first();

        if (!$tier) {
            return $this->error('Ticket tier not found.', null, 404);
        }

        // Check availability
        if ($tier->quantity_available !== null) {
            $remaining = $tier->quantity_available - $tier->quantity_sold;
            if ($remaining < $request->quantity) {
                return $this->error(null, 'Not enough tickets available in this tier.', 400);
            }
        }

        try {
            return DB::transaction(function () use ($request, $event, $tier) {
                $unitPrice = $tier->price;
                $quantity = $request->quantity;
                $subtotal = $unitPrice * $quantity;

                // 5% Service Fee as requested/shown in screenshot
                $serviceFeePercent = 0.05;
                $serviceFee = $subtotal * $serviceFeePercent;
                $total = $subtotal + $serviceFee;

                $registration = EventRegistration::create([
                    'event_id' => $event->id,
                    'ticket_tier_id' => $tier->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number,
                    'user_id' => auth('api')->id(),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'service_fee' => $serviceFee,
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'currency' => 'USD',
                    'payment_status' => 'pending',
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                // Update quantity sold
                $tier->increment('quantity_sold', $quantity);

                return $this->success(
                    'Registration successful.',
                    [
                        'booking_reference' => $registration->booking_reference,
                        'registration' => $registration->load('event', 'ticketTier'),
                    ],
                    201
                );
            });
        } catch (Exception $e) {
            Log::error('Event registration failed: ' . $e->getMessage());
            return $this->error(null, 'Failed to process registration: ' . $e->getMessage());
        }
    }
}
