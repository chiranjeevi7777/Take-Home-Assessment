<?php

namespace App\Services;

use App\Contracts\EmbeddingProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for the Python embedding service.
 *
 * Implements the EmbeddingProvider contract so Laravel services
 * can interact with embeddings without knowing the transport layer.
 */
class EmbeddingClient implements EmbeddingProvider
{
    private string $baseUrl;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $this->baseUrl = config('embedding.service_url');
        $this->timeout = config('embedding.timeout');
        $this->retries = config('embedding.retries');
    }

    public function embedPost(int $postId, string $content): ?string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retries, 100)
                ->post("{$this->baseUrl}/embed", [
                    'post_id' => $postId,
                    'content' => $content,
                ]);

            if ($response->successful()) {
                return $response->json('embedding_id');
            }

            Log::warning('Embedding generation failed', [
                'post_id' => $postId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Embedding service unavailable', [
                'post_id' => $postId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function search(string $query, int $limit = 20): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retries, 100)
                ->get("{$this->baseUrl}/search", [
                    'q' => $query,
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                $results = [];
                foreach ($response->json() as $item) {
                    $results[(int) $item['post_id']] = (float) $item['score'];
                }
                return $results;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Embedding search failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getSimilarityScores(int $userId, array $postIds): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retries, 100)
                ->post("{$this->baseUrl}/similarity", [
                    'user_id' => $userId,
                    'post_ids' => $postIds,
                ]);

            if ($response->successful()) {
                $scores = [];
                foreach ($response->json() as $item) {
                    $scores[(int) $item['post_id']] = (float) $item['score'];
                }
                return $scores;
            }

            // Fallback: default similarity for all posts
            return array_fill_keys($postIds, config('ranking.default_similarity'));
        } catch (\Exception $e) {
            Log::error('Similarity scoring failed', ['error' => $e->getMessage()]);
            return array_fill_keys($postIds, config('ranking.default_similarity'));
        }
    }

    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/health");

            return $response->successful()
                && $response->json('status') === 'healthy';
        } catch (\Exception $e) {
            return false;
        }
    }
}
