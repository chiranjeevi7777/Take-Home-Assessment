<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'authenticity_score',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'authenticity_score' => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    /**
     * Users this user follows.
     */
    public function following(): HasMany
    {
        return $this->hasMany(Relationship::class, 'follower_id');
    }

    /**
     * Users who follow this user.
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Relationship::class, 'following_id');
    }

    // ── Helpers ────────────────────────────────────────────

    /**
     * Get the relationship strength with another user.
     * Returns 0.0 if no relationship exists.
     */
    public function relationshipStrengthWith(int $userId): float
    {
        $relationship = $this->following()
            ->where('following_id', $userId)
            ->first();

        return $relationship ? (float) $relationship->strength : 0.0;
    }
}
