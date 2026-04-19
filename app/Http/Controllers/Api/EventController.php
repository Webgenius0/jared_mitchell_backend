<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicketTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
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

        $events = $query->latest('starts_at')->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
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
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $event,
        ]);
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
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $event = Event::findOrFail($request->event_id);
        $tier = EventTicketTier::where('id', $request->ticket_tier_id)
            ->where('event_id', $event->id)
            ->firstOrFail();

        // Check availability
        if ($tier->quantity_available !== null) {
            $remaining = $tier->quantity_available - $tier->quantity_sold;
            if ($remaining < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough tickets available in this tier.'
                ], 400);
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
                    'user_id' => null, // If logged in
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'service_fee' => $serviceFee,
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'currency' => 'USD',
                    'payment_status' => 'pending', // Would move to paid after payment integration
                    'status' => 'confirmed',     // Automatically confirmed for now
                    'confirmed_at' => now(),
                ]);

                // Update quantity sold
                $tier->increment('quantity_sold', $quantity);

                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful.',
                    'data' => [
                        'booking_reference' => $registration->booking_reference,
                        'registration' => $registration->load('event', 'ticketTier'),
                    ]
                ], 201);
            });
        } catch (\Exception $e) {

        Log::error('Event registration failed: ' . $e->getMessage() . $e->getTraceAsString(). ' -- Payload: ' . json_encode($request->all()));
            return response()->json([
                'success' => false,
                'message' => 'Failed to process registration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upcomingEvents()
    {
        $events = Event::where('status', 'published')
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->take(15)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function pastEvents()
    {
        $events = Event::where('status', 'published')
            ->where('starts_at', '<', now())
            ->orderBy('starts_at', 'desc')
            ->take(15)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }
}
