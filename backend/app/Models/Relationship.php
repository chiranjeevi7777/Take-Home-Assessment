<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Relationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'following_id',
        'strength',
    ];

    protected function casts(): array
    {
        return [
            'strength' => 'decimal:2',
        ];
    }

    public const MAX_STRENGTH = 10.0;
    public const INTERACTION_INCREMENT = 0.5;

    // ── Relationships ──────────────────────────────────────

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    // ── Methods ────────────────────────────────────────────

    /**
     * Increment relationship strength (capped at MAX_STRENGTH).
     */
    public function incrementStrength(): void
    {
        $newStrength = min(
            (float) $this->strength + self::INTERACTION_INCREMENT,
            self::MAX_STRENGTH
        );

        $this->update(['strength' => $newStrength]);
    }
}
