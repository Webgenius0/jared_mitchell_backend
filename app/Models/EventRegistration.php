<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_reference',
        'status',
        'event_id',
        'ticket_tier_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'quantity',
        'unit_price',
        'service_fee',
        'subtotal',
        'total',
        'currency',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_customer_id',
        'stripe_charge_id',
        'stripe_refund_id',
        'payment_status',
        'paid_at',
        'confirmed_at',
        'checked_in_at',
        'cancelled_at',
        'refunded_at',
        'failed_at',
        'qr_code',
        'cancellation_reason',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketTier()
    {
        return $this->belongsTo(EventTicketTier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::creating(function (self $registration) {

            if (empty($registration->booking_reference)) {
                $registration->booking_reference = self::generateBookingReference();
            }

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public static function generateBookingReference(): string
    {
        return sprintf(
            'EVT-%s-%s',
            now()->format('Ymd'),
            strtoupper(Str::random(6))
        );
    }
}
