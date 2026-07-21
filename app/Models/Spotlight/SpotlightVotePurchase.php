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
        'spotlight_vote_package_id',
        'package',
        'votes_count',
        'amount_paid',
        'order_id',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'status',
        'approved_by',
        'approved_at',
        'paid_at',
        'admin_notes',
    ];

    protected $casts = [
        'amount_paid'  => 'decimal:2',
        'votes_count'  => 'integer',
        'approved_at'  => 'datetime',
        'paid_at'      => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING   = 'pending';   // User submitted request
    public const STATUS_APPROVED  = 'approved';  // Admin approved, awaiting payment
    public const STATUS_PAID      = 'paid';      // Payment received via Stripe, votes credited
    public const STATUS_REFUNDED  = 'refunded';  // Refunded by admin, votes removed
    public const STATUS_CANCELLED = 'cancelled'; // Cancelled before payment

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function package(): BelongsTo
    {
        return $this->belongsTo(SpotlightVotePackage::class, 'spotlight_vote_package_id');
    }

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
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    public function scopeAwaitingPayment($query)
    {
        return $query->whereIn('status', [self::STATUS_APPROVED, self::STATUS_PENDING]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Whether the user can pay for this purchase (approved but not yet paid).
     */
    public function isPayable(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Get the package details.
     *
     * Maintained for backward compatibility with code that references
     * the old hardcoded PACKAGES constant. New code should use the
     * package() relationship instead.
     *
     * @deprecated Use $purchase->package relationship instead.
     */
    public static function packageDetails(string $package): ?array
    {
        $pkg = static::packageModel($package);
        return $pkg ? ['votes' => $pkg->votes_count, 'price' => $pkg->price, 'label' => $pkg->label] : null;
    }

    /**
     * Resolve a SpotlightVotePackage model by slug for backward compatibility.
     */
    private static function packageModel(string $slug): ?SpotlightVotePackage
    {
        return SpotlightVotePackage::where('slug', $slug)->first();
    }
}
