<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSecurityToken extends Model
{
    protected $table = 'user_security_tokens';

    protected $fillable = [
        'user_id',
        'identifier',
        'token_hash',
        'type',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    /*
    |----------------------------------------------------------------------
    | Token Type Constants
    |----------------------------------------------------------------------
    */

    const TYPE_EMAIL_VERIFICATION = 'email_verification';
    const TYPE_PASSWORD_RESET     = 'password_reset';
    const TYPE_LOGIN_OTP          = 'login_otp';
    const TYPE_2FA                = '2fa';

    /*
    |----------------------------------------------------------------------
    | Relationships
    |----------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |----------------------------------------------------------------------
    | Scopes
    |----------------------------------------------------------------------
    */

    /**
     * Valid = not used + not expired + matches type & identifier
     */
    public function scopeValid($query, string $identifier, string $type): mixed
    {
        return $query->where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Throttle check — was a token created within last N seconds?
     */
    public function scopeRecentlySent($query, string $identifier, string $type, int $seconds = 60): mixed
    {
        return $query->where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where('created_at', '>', now()->subSeconds($seconds));
    }

    /*
    |----------------------------------------------------------------------
    | Helpers
    |----------------------------------------------------------------------
    */

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return ! is_null($this->used_at);
    }

    /**
     * Mark token as consumed right now.
     */
    public function markUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Invalidate all active tokens for an identifier + type combo.
     * Call this before issuing a new token.
     */
    public static function invalidatePrevious(string $identifier, string $type): void
    {
        static::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }
}
