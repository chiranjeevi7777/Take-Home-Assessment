<?php

namespace App\Contracts;

/**
 * Interface for ranking strategies.
 * Allows swapping ranking algorithms without changing consumers.
 */
interface RankingStrategy
{
    /**
     * Score a post for a given viewer.
     *
     * @param array $post         Post data including author info
     * @param int   $viewerId     The authenticated user viewing the feed
     * @param array $signals      Pre-computed signals (similarity, relationship, etc.)
     * @return float              Combined ranking score (0-1)
     */
    public function score(array $post, int $viewerId, array $signals): float;
}
