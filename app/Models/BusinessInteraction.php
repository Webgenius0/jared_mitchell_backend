<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'action_type',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'action_type' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
