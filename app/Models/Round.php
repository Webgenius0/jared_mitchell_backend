<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Round extends Model
{
    use HasFactory;
    protected $fillable = [
        'round_session_id',
        'round_number',
        'title',
        'goal',
        'requirements',
        'is_active',
        'advance_limit',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'advance_limit' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function roundSession(): BelongsTo
    {
        return $this->belongsTo(RoundSession::class);
    }
}
