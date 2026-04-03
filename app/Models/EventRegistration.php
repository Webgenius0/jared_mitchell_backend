<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventRegistration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_reference', 'event_id', 'ticket_tier_id', 'first_name',
        'last_name', 'email', 'phone_number', 'user_id', 'quantity',
        'unit_price', 'service_fee', 'subtotal', 'total', 'currency',
        'payment_status', 'payment_intent_id', 'payment_method',
        'paid_at', 'status', 'confirmed_at', 'cancelled_at',
        'cancellation_reason', 'qr_code', 'checked_in_at'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketTier()
    {
        return $this->belongsTo(EventTicketTier::class, 'ticket_tier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($registration) {
            if (!$registration->booking_reference) {
                $registration->booking_reference = 'OSI-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });
    }
}
