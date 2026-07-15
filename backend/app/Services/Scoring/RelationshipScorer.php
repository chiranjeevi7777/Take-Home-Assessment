<?php

namespace App\Services\Scoring;

use App\Models\Relationship;

/**
 * Computes the relationship signal between a viewer and a post author.
 *
 * Based on the relationship strength value which is incremented
 * each time the viewer interacts with the author's content.
 *
 * Strength range: [0, MAX_STRENGTH] → normalized to [0, 1]
 *
 * If no relationship exists, returns 0.0 (stranger).
 */
class RelationshipScorer
{
    /**
     * Compute normalized relationship score.
     *
     * @param float $strength Raw relationship strength (0 - MAX_STRENGTH)
     * @return float Score in [0, 1]
     */
    public function score(float $strength): float
    {
        return min($strength / Relationship::MAX_STRENGTH, 1.0);
    }

    /**
     * Batch compute relationship scores for multiple authors.
     *
     * @param int   $viewerId  The user viewing the feed
     * @param int[] $authorIds Authors to compute scores for
     * @return array<int, float> [author_id => normalized_score]
     */
    public function batchScore(int $viewerId, array $authorIds): array
    {
        $relationships = Relationship::where('follower_id', $viewerId)
            ->whereIn('following_id', $authorIds)
            ->pluck('strength', 'following_id');

        $scores = [];
        foreach ($authorIds as $authorId) {
            $raw = (float) ($relationships[$authorId] ?? 0.0);
            $scores[$authorId] = $this->score($raw);
        }

        return $scores;
    }
}
