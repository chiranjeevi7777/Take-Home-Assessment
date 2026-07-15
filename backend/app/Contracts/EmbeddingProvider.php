<?php

namespace App\Contracts;

/**
 * Interface for embedding provider clients.
 * Abstracts communication with the Python embedding service.
 */
interface EmbeddingProvider
{
    /**
     * Generate and store an embedding for a post.
     */
    public function embedPost(int $postId, string $content): ?string;

    /**
     * Search for posts similar to a query string.
     *
     * @return array<int, float> [post_id => similarity_score]
     */
    public function search(string $query, int $limit = 20): array;

    /**
     * Get similarity scores between a user's interest vector and given posts.
     *
     * @param int   $userId
     * @param int[] $postIds
     * @return array<int, float> [post_id => similarity_score]
     */
    public function getSimilarityScores(int $userId, array $postIds): array;

    /**
     * Check if the embedding service is available.
     */
    public function isHealthy(): bool;
}
