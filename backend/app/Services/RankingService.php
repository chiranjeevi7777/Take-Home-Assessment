<?php

namespace App\Services;

use App\Contracts\RankingStrategy;
use App\Services\Scoring\AuthenticityScorer;
use App\Services\Scoring\RecencyScorer;
use App\Services\Scoring\RelationshipScorer;
use App\Services\Scoring\SimilarityScorer;
use Carbon\Carbon;

/**
 * Combines four isolated scoring signals into one weighted ranking score.
 *
 * S(post, viewer) = w_a×A + w_r×R + w_s×Sim + w_t×T
 *
 * Each signal is computed by its dedicated scorer module.
 * Weights come from config/ranking.php and are adjustable via env vars.
 */
class RankingService implements RankingStrategy
{
    private array $weights;

    public function __construct(
        private AuthenticityScorer $authenticityScorer,
        private RelationshipScorer $relationshipScorer,
        private SimilarityScorer $similarityScorer,
        private RecencyScorer $recencyScorer,
    ) {
        $this->weights = config('ranking.weights');
    }

    // ── Composite Scoring ──────────────────────────────────

    /**
     * Compute the final weighted score from pre-computed signal values.
     *
     * @param array $signals ['authenticity' => 0-1, 'relationship' => 0-1, 'similarity' => 0-1, 'recency' => 0-1]
     * @return float Weighted composite score
     */
    public function computeScore(array $signals): float
    {
        return ($this->weights['authenticity'] * ($signals['authenticity'] ?? 0))
             + ($this->weights['relationship'] * ($signals['relationship'] ?? 0))
             + ($this->weights['similarity']   * ($signals['similarity'] ?? 0))
             + ($this->weights['recency']      * ($signals['recency'] ?? 0));
    }

    /**
     * RankingStrategy contract implementation.
     */
    public function score(array $post, int $viewerId, array $signals): float
    {
        return $this->computeScore($signals);
    }

    // ── Individual Signal Accessors ────────────────────────

    /**
     * Compute authenticity signal for ranking (normalized 0-1).
     */
    public function scoreAuthenticity(float $authorScore, string $content): float
    {
        return $this->authenticityScorer->score($authorScore, $content);
    }

    /**
     * Compute raw authenticity score for post storage (0-100).
     */
    public function computeRawAuthenticity(float $authorScore, string $content): float
    {
        return $this->authenticityScorer->computeRawScore($authorScore, $content);
    }

    /**
     * Batch compute relationship scores.
     *
     * @return array<int, float> [author_id => normalized_score]
     */
    public function scoreRelationships(int $viewerId, array $authorIds): array
    {
        return $this->relationshipScorer->batchScore($viewerId, $authorIds);
    }

    /**
     * Batch compute similarity scores.
     *
     * @return array<int, float> [post_id => similarity_score]
     */
    public function scoreSimilarity(int $viewerId, array $postIds): array
    {
        return $this->similarityScorer->batchScore($viewerId, $postIds);
    }

    /**
     * Compute time decay for a single post.
     */
    public function scoreRecency(Carbon $createdAt): float
    {
        return $this->recencyScorer->score($createdAt);
    }

    // ── Introspection ──────────────────────────────────────

    /**
     * Get current weight configuration.
     */
    public function getWeights(): array
    {
        return $this->weights;
    }

    /**
     * Explain a ranking score by showing each signal's contribution.
     * Useful for debugging and transparency.
     */
    public function explain(array $signals): array
    {
        $contributions = [];
        $total = 0;

        foreach ($this->weights as $signal => $weight) {
            $value = $signals[$signal] ?? 0;
            $contribution = $weight * $value;
            $total += $contribution;

            $contributions[$signal] = [
                'raw_value' => round($value, 4),
                'weight' => $weight,
                'contribution' => round($contribution, 4),
            ];
        }

        $contributions['total'] = round($total, 4);

        return $contributions;
    }
}
