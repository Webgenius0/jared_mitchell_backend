<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTicketTier extends Model
{
    protected $fillable = [
        'event_id', 'name', 'description', 'price', 'service_fee',
        'quantity_available', 'quantity_sold', 'sale_starts_at',
        'sale_ends_at', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'sale_starts_at' => 'datetime',
        'sale_ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
