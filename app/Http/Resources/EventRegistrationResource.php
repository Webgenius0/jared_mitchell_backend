<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventRegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            
            'event' => [
                'id' => $this->event->id ?? null,
                'title' => $this->event->title ?? null,
                'slug' => $this->event->slug ?? null,
                'cover_image' => $this->event->cover_image_path ? url('/' . $this->event->cover_image_path) : null,
                'starts_at' => $this->event->starts_at?->toIso8601String(),
                'address' => $this->event->address ?? null,
                'venue' => $this->event->venue_name ?? null,
            ],
            
            'ticket_tier' => [
                'name' => $this->ticketTier->name ?? 'Standard',
            ],

            'attendee' => [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone_number' => $this->phone_number,
            ],
            
            'billing' => [
                'quantity' => $this->quantity,
                'unit_price' => (float) $this->unit_price,
                'service_fee' => (float) $this->service_fee,
                'total' => (float) $this->total,
                'currency' => $this->currency,
            ],
            
            'timeline' => [
                'created_at' => $this->created_at->toIso8601String(),
                'paid_at' => $this->paid_at?->toIso8601String(),
                'confirmed_at' => $this->confirmed_at?->toIso8601String(),
                'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            ]
        ];
    }
}
