<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInteractionRequest;
use App\Models\Interaction;
use App\Models\Relationship;
use Illuminate\Http\JsonResponse;

class InteractionController extends Controller
{
    /**
     * POST /api/interactions
     *
     * Toggle a reaction on a post.
     * - If the reaction exists, remove it (toggle off).
     * - If it doesn't exist, create it.
     * - Strengthen the relationship between the viewer and post author.
     */
    public function store(StoreInteractionRequest $request): JsonResponse
    {
        $user = $request->user();
        $postId = $request->validated('post_id');
        $type = $request->validated('type');

        // Check for existing reaction
        $existing = Interaction::where('user_id', $user->id)
            ->where('post_id', $postId)
            ->where('type', $type)
            ->first();

        if ($existing) {
            // Toggle off
            $existing->delete();

            return response()->json([
                'success' => true,
                'data' => [
                    'post_id' => $postId,
                    'type' => $type,
                    'action' => 'removed',
                ],
                'message' => 'Reaction removed',
            ]);
        }

        // Create reaction
        Interaction::create([
            'user_id' => $user->id,
            'post_id' => $postId,
            'type' => $type,
        ]);

        // Strengthen relationship with the post author
        $this->strengthenRelationship($user->id, $postId);

        return response()->json([
            'success' => true,
            'data' => [
                'post_id' => $postId,
                'type' => $type,
                'action' => 'created',
            ],
            'message' => 'Reaction recorded',
        ], 201);
    }

    /**
     * Increment the relationship strength between the interacting user
     * and the post author. Creates the relationship if it doesn't exist.
     */
    private function strengthenRelationship(int $userId, int $postId): void
    {
        $post = \App\Models\Post::find($postId);
        if (! $post || $post->user_id === $userId) {
            return; // Don't strengthen relationship with self
        }

        $relationship = Relationship::firstOrCreate(
            [
                'follower_id' => $userId,
                'following_id' => $post->user_id,
            ],
            ['strength' => 0.0],
        );

        $relationship->incrementStrength();
    }
}
