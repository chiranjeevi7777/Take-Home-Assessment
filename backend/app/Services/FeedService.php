<?php

namespace App\Services;

use App\Models\Interaction;
use App\Models\Post;

/**
 * Orchestrates feed generation by fetching candidate posts,
 * gathering ranking signals, and returning scored results.
 */
class FeedService
{
    public function __construct(
        private RankingService $rankingService,
    ) {}

    /**
     * Get a ranked, paginated feed for the authenticated user.
     *
     * Pipeline: Candidates → Signals → Rank → Paginate
     */
    public function getFeed(int $viewerId, int $page = 1, int $perPage = 20): array
    {
        $poolSize = config('ranking.candidate_pool_size');
        $perPage = min($perPage, config('ranking.per_page_max'));

        // 1. Fetch candidate posts with eager-loaded relationships
        $candidates = Post::with(['user', 'interactions'])
            ->withCount('interactions')
            ->recent($poolSize)
            ->get();

        if ($candidates->isEmpty()) {
            return $this->emptyResult($page, $perPage);
        }

        // 2. Gather signals
        $postIds = $candidates->pluck('id')->toArray();
        $authorIds = $candidates->pluck('user_id')->unique()->toArray();

        $relationshipStrengths = $this->rankingService->scoreRelationships($viewerId, $authorIds);
        $similarityScores = $this->rankingService->scoreSimilarity($viewerId, $postIds);
        $userReactions = $this->getUserReactions($viewerId, $postIds);

        // 3. Score and rank each post
        $scored = $candidates->map(function (Post $post) use (
            $viewerId,
            $relationshipStrengths,
            $similarityScores,
        ) {
            $signals = [
                'authenticity' => $this->rankingService->scoreAuthenticity(
                    (float) $post->user->authenticity_score, $post->content
                ),
                'relationship' => $relationshipStrengths[$post->user_id] ?? 0.0,
                'similarity' => $similarityScores[$post->id] ?? config('ranking.default_similarity'),
                'recency' => $this->rankingService->scoreRecency($post->created_at),
            ];

            $score = $this->rankingService->computeScore($signals);

            return [
                'post' => $post,
                'score' => $score,
            ];
        });

        // 4. Sort by score descending
        $ranked = $scored->sortByDesc('score')->values();

        // 5. Paginate
        $total = $ranked->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        $pageItems = $ranked->slice($offset, $perPage)->values();

        // 6. Build response
        $data = $pageItems->map(function ($item) use ($userReactions) {
            $post = $item['post'];
            return [
                'id' => $post->id,
                'content' => $post->content,
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'authenticity_score' => (float) $post->user->authenticity_score,
                ],
                'authenticity_score' => (float) $post->authenticity_score,
                'ranking_score' => round($item['score'], 4),
                'reactions_count' => $post->interactions_count,
                'user_reaction' => $userReactions[$post->id] ?? null,
                'created_at' => $post->created_at->toIso8601String(),
            ];
        })->toArray();

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }



    /**
     * Get the viewer's reactions to a set of posts.
     *
     * @return array<int, string> [post_id => reaction_type]
     */
    private function getUserReactions(int $viewerId, array $postIds): array
    {
        return Interaction::where('user_id', $viewerId)
            ->whereIn('post_id', $postIds)
            ->pluck('type', 'post_id')
            ->toArray();
    }

    private function emptyResult(int $page, int $perPage): array
    {
        return [
            'data' => [],
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => 0,
                'last_page' => 1,
            ],
        ];
    }
}
