<?php

namespace App\Http\Controllers;

use App\Contracts\EmbeddingProvider;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Services\RankingService;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function __construct(
        private EmbeddingProvider $embeddingProvider,
        private RankingService $rankingService,
    ) {}

    /**
     * POST /api/posts
     *
     * Create a new post and trigger async embedding generation.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $user = $request->user();

        // Compute post-level authenticity via the isolated scorer
        $authenticityScore = $this->rankingService->computeRawAuthenticity(
            (float) $user->authenticity_score,
            $request->validated('content'),
        );

        $post = Post::create([
            'user_id' => $user->id,
            'content' => $request->validated('content'),
            'authenticity_score' => $authenticityScore,
        ]);

        // Generate embedding (fire-and-forget in production; synchronous here)
        $embeddingId = $this->embeddingProvider->embedPost($post->id, $post->content);
        if ($embeddingId) {
            $post->update(['embedding_id' => $embeddingId]);
        }

        $post->load('user');
        $post->loadCount('interactions');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $post->id,
                'content' => $post->content,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'authenticity_score' => (float) $post->authenticity_score,
                'reactions_count' => 0,
                'created_at' => $post->created_at->toIso8601String(),
            ],
            'message' => 'Post created successfully',
        ], 201);
    }

}
