<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = 'api';

    protected $fillable = [
        'email',
        'phone',
        'password',
        'status',
        'email_verified_at',
        'artist_category_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /*
    |----------------------------------------------------------------------
    | JWT Interface Methods
    |----------------------------------------------------------------------
    */

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role'   => $this->getRoleNames()->first(),
            'status' => $this->status,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function artistCategory(): BelongsTo
    {
        return $this->belongsTo(ArtistCategory::class, 'artist_category_id');
    }

    /**
     * Users who liked this artist.
     */
    public function likers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'artist_likes', 'artist_id', 'user_id')->withTimestamps();
    }

    /**
     * Artists liked by this user.
     */
    public function likedArtists(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'artist_likes', 'user_id', 'artist_id')->withTimestamps();
    }

    /**
     * Users who bookmarked this artist.
     */
    public function bookmarkers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'artist_bookmarks', 'artist_id', 'user_id')->withTimestamps();
    }

    /**
     * Artists bookmarked by this user.
     */
    public function bookmarkedArtists(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'artist_bookmarks', 'user_id', 'artist_id')->withTimestamps();
    }

    /**
     * Shares received by this artist.
     */
    public function shares(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArtistShare::class, 'artist_id');
    }
}
