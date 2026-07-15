<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService,
    ) {}

    /**
     * GET /api/search?q=
     *
     * Semantic search with LIKE-based fallback.
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $result = $this->searchService->search(
            query: $request->validated('q'),
            viewerId: $request->user()->id,
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 20),
        );

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'meta' => $result['meta'],
            'message' => 'Search results retrieved successfully',
        ]);
    }
}
