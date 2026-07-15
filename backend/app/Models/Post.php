<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'embedding_id',
        'authenticity_score',
    ];

    protected function casts(): array
    {
        return [
            'authenticity_score' => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    /**
     * Scope to recent posts within a candidate pool window.
     */
    public function scopeRecent($query, int $limit = 200)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope to posts that have been embedded.
     */
    public function scopeEmbedded($query)
    {
        return $query->whereNotNull('embedding_id');
    }

    // ── Accessors ──────────────────────────────────────────

    /**
     * Get the reaction count for this post.
     */
    public function getReactionsCountAttribute(): int
    {
        return $this->interactions()->count();
    }
}
