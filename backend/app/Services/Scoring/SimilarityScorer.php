<?php

namespace App\Services\Scoring;

use App\Contracts\EmbeddingProvider;

/**
 * Computes the semantic similarity signal between a viewer's interests
 * and a post's content.
 *
 * Delegates to the embedding service for cosine similarity computation.
 * Returns a configurable default when the service is unavailable or
 * the viewer has no interaction history.
 *
 * Output: score in [0, 1]
 */
class SimilarityScorer
{
    private float $defaultScore;

    public function __construct(
        private EmbeddingProvider $embeddingProvider,
    ) {
        $this->defaultScore = config('ranking.default_similarity');
    }

    /**
     * Get similarity scores for a batch of posts.
     *
     * @param int   $viewerId The user viewing the feed
     * @param int[] $postIds  Posts to score
     * @return array<int, float> [post_id => similarity_score]
     */
    public function batchScore(int $viewerId, array $postIds): array
    {
        if (empty($postIds)) {
            return [];
        }

        $scores = $this->embeddingProvider->getSimilarityScores($viewerId, $postIds);

        // Ensure all requested posts have a score
        foreach ($postIds as $postId) {
            if (! isset($scores[$postId])) {
                $scores[$postId] = $this->defaultScore;
            }
        }

        return $scores;
    }

    /**
     * Get the default similarity score used when no data is available.
     */
    public function getDefaultScore(): float
    {
        return $this->defaultScore;
    }
}
