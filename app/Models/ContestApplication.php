<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'round_session_id',
        'status',
        'approved_at',
        'approved_by',
        'admin_note',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /*
    |------------------------------------------------------------------------
    | Relationships
    |------------------------------------------------------------------------
    */

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function roundSession(): BelongsTo
    {
        return $this->belongsTo(RoundSession::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
