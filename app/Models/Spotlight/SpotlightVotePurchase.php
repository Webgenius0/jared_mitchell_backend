<?php

namespace App\Models\Spotlight;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotlightVotePurchase extends Model
{
    protected $fillable = [
        'spotlight_week_nominee_id',
        'user_id',
        'package',
        'votes_count',
        'amount_paid',
        'order_id',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'amount_paid'  => 'decimal:2',
        'votes_count'  => 'integer',
        'approved_at'  => 'datetime',
    ];

    /**
     * Pricing configuration. Centralized here as the single source of truth.
     */
    public const PACKAGES = [
        'starter' => ['votes' => 1,  'price' => 1.00,  'label' => '$1 = 1 vote'],
        'popular' => ['votes' => 10, 'price' => 8.00,  'label' => '$8 = 10 votes'],
        'boost'   => ['votes' => 25, 'price' => 18.00, 'label' => '$18 = 25 votes'],
        'power'   => ['votes' => 50, 'price' => 35.00, 'label' => '$35 = 50 votes'],
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function nominee(): BelongsTo
    {
        return $this->belongsTo(SpotlightWeekNominee::class, 'spotlight_week_nominee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the package details for a given package key.
     */
    public static function packageDetails(string $package): ?array
    {
        return self::PACKAGES[$package] ?? null;
    }
}
