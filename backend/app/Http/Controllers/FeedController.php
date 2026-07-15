<?php

namespace App\Http\Controllers;

use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __construct(
        private FeedService $feedService,
    ) {}

    /**
     * GET /api/feed
     *
     * Returns a ranked, paginated feed for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
        ]);

        $result = $this->feedService->getFeed(
            viewerId: $request->user()->id,
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', config('ranking.per_page_default')),
        );

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'meta' => $result['meta'],
            'message' => 'Feed retrieved successfully',
        ]);
    }
}
