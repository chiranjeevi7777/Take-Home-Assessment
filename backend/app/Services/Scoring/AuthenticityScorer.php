<?php

namespace App\Services\Scoring;

/**
 * Computes the authenticity signal for a post.
 *
 * Combines the author's platform-level authenticity score
 * with content-level heuristics:
 *   - Content length (substantive posts score higher)
 *   - Vocabulary diversity (genuine writing has higher unique word ratios)
 *   - No excessive caps (shouting/clickbait penalty)
 *
 * Output: normalized score in [0, 1]
 */
class AuthenticityScorer
{
    private const MIN_SUBSTANTIVE_LENGTH = 50;
    private const DIVERSITY_THRESHOLD = 0.7;
    private const CAPS_PENALTY_THRESHOLD = 0.4;

    /**
     * Compute normalized authenticity score for ranking.
     *
     * @param float  $authorScore Author's platform authenticity (0-100)
     * @param string $content     Post content text
     * @return float Score in [0, 1]
     */
    public function score(float $authorScore, string $content): float
    {
        $base = $authorScore / 100.0;
        $contentFactor = $this->contentQualityFactor($content);

        return $this->clamp($base * $contentFactor);
    }

    /**
     * Compute raw authenticity score for storage (0-100 scale).
     * Used when creating posts.
     *
     * @param float  $authorScore Author's platform authenticity (0-100)
     * @param string $content     Post content text
     * @return float Score in [0, 100]
     */
    public function computeRawScore(float $authorScore, string $content): float
    {
        $base = $authorScore;

        if (strlen($content) > self::MIN_SUBSTANTIVE_LENGTH) {
            $base = min($base + 3, 100);
        }

        $diversityRatio = $this->uniqueWordRatio($content);
        if ($diversityRatio > self::DIVERSITY_THRESHOLD) {
            $base = min($base + 2, 100);
        }

        // Penalty for excessive capitalization (clickbait signal)
        if ($this->capsRatio($content) > self::CAPS_PENALTY_THRESHOLD) {
            $base = max($base - 5, 0);
        }

        return round($base, 2);
    }

    /**
     * Content quality multiplier [0.8, 1.2].
     */
    private function contentQualityFactor(string $content): float
    {
        $factor = 1.0;

        // Substantive content bonus
        if (strlen($content) > self::MIN_SUBSTANTIVE_LENGTH) {
            $factor += 0.1;
        }

        // Vocabulary diversity bonus
        if ($this->uniqueWordRatio($content) > self::DIVERSITY_THRESHOLD) {
            $factor += 0.1;
        }

        // Caps penalty
        if ($this->capsRatio($content) > self::CAPS_PENALTY_THRESHOLD) {
            $factor -= 0.2;
        }

        return max(0.8, min($factor, 1.2));
    }

    private function uniqueWordRatio(string $content): float
    {
        $words = str_word_count(strtolower($content), 1);
        if (count($words) === 0) {
            return 0.0;
        }
        return count(array_unique($words)) / count($words);
    }

    private function capsRatio(string $content): float
    {
        $alpha = preg_replace('/[^a-zA-Z]/', '', $content);
        if (strlen($alpha) === 0) {
            return 0.0;
        }
        $upper = preg_replace('/[^A-Z]/', '', $content);
        return strlen($upper) / strlen($alpha);
    }

    private function clamp(float $value): float
    {
        return max(0.0, min($value, 1.0));
    }
}
