<?php

namespace App\Services\Scoring;

use Carbon\Carbon;

/**
 * Computes the time decay (recency) signal for a post.
 *
 * Uses exponential decay: exp(-λ × hours_since_post)
 *
 * Decay rate λ is configurable via config/ranking.php.
 *
 * Example values at default λ = 0.05:
 *   0h  → 1.000    (brand new)
 *   6h  → 0.741
 *   12h → 0.549
 *   24h → 0.301
 *   48h → 0.091
 *   72h → 0.027    (nearly invisible)
 *
 * Output: score in (0, 1]
 */
class RecencyScorer
{
    private float $decayRate;

    public function __construct()
    {
        $this->decayRate = config('ranking.decay_rate');
    }

    /**
     * Compute recency score for a single post.
     *
     * @param Carbon $createdAt Post creation timestamp
     * @return float Score in (0, 1]
     */
    public function score(Carbon $createdAt): float
    {
        $hoursSince = $this->hoursSince($createdAt);

        return exp(-$this->decayRate * $hoursSince);
    }

    /**
     * Batch compute recency scores.
     *
     * @param array<int, Carbon> $timestamps [post_id => created_at]
     * @return array<int, float> [post_id => recency_score]
     */
    public function batchScore(array $timestamps): array
    {
        $scores = [];
        foreach ($timestamps as $postId => $createdAt) {
            $scores[$postId] = $this->score($createdAt);
        }
        return $scores;
    }

    /**
     * Get hours elapsed since a timestamp (minimum 0).
     */
    private function hoursSince(Carbon $timestamp): float
    {
        if ($timestamp->isAfter(now())) {
            return 0.0;
        }
        return now()->diffInMinutes($timestamp, true) / 60;
    }

    /**
     * Get the configured decay rate.
     */
    public function getDecayRate(): float
    {
        return $this->decayRate;
    }
}
