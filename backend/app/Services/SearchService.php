<?php

namespace App\Services;

use App\Contracts\EmbeddingProvider;
use App\Models\Post;

/**
 * Handles semantic search by delegating to the embedding service
 * and enriching results with post data from the database.
 */
class SearchService
{
    public function __construct(
        private EmbeddingProvider $embeddingProvider,
    ) {}

    /**
     * Perform semantic search and return enriched, paginated results.
     */
    public function search(string $query, int $viewerId, int $page = 1, int $perPage = 20): array
    {
        // 1. Get similarity-ranked post IDs from embedding service
        $maxResults = min($page * $perPage + $perPage, 100); // Fetch enough for pagination
        try {
            $similarityScores = $this->embeddingProvider->search($query, $maxResults);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Embedding search failed, falling back to LIKE search', [
                'error' => $e->getMessage()
            ]);
            $similarityScores = [];
        }

        if (empty($similarityScores)) {
            return $this->fallbackSearch($query, $page, $perPage);
        }

        $postIds = array_keys($similarityScores);

        // 2. Fetch full post data
        $posts = Post::with('user')
            ->withCount('interactions')
            ->whereIn('id', $postIds)
            ->get()
            ->keyBy('id');

        // 3. Build ordered results (preserving similarity ranking)
        $results = [];
        foreach ($similarityScores as $postId => $score) {
            if (! isset($posts[$postId])) {
                continue;
            }

            $post = $posts[$postId];
            $results[] = [
                'id' => $post->id,
                'content' => $post->content,
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'authenticity_score' => (float) $post->user->authenticity_score,
                ],
                'authenticity_score' => (float) $post->authenticity_score,
                'similarity_score' => round($score, 4),
                'reactions_count' => $post->interactions_count,
                'created_at' => $post->created_at->toIso8601String(),
            ];
        }

        // 4. Paginate
        $total = count($results);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        $pageItems = array_slice($results, $offset, $perPage);

        return [
            'data' => $pageItems,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }

    /**
     * Fallback to LIKE-based search when embedding service is unavailable.
     */
    private function fallbackSearch(string $query, int $page, int $perPage): array
    {
        $paginator = Post::with('user')
            ->withCount('interactions')
            ->where('content', 'like', "%{$query}%")
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'authenticity_score' => (float) $post->user->authenticity_score,
                ],
                'authenticity_score' => (float) $post->authenticity_score,
                'similarity_score' => null,
                'reactions_count' => $post->interactions_count,
                'created_at' => $post->created_at->toIso8601String(),
            ];
        })->toArray();

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
