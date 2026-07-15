"""
Search and similarity API routes.
"""

from fastapi import APIRouter, HTTPException, Query

from ..models.schemas import SearchResult, SimilarityRequest, SimilarityScore
from .embeddings import get_service

router = APIRouter(tags=["search"])


@router.get("/search", response_model=list[SearchResult])
async def semantic_search(
    q: str = Query(min_length=2, max_length=500),
    limit: int = Query(default=20, ge=1, le=100),
):
    """Semantic search — find posts by meaning."""
    try:
        service = get_service()
        results = service.search(query=q, limit=limit)
        return [
            SearchResult(
                post_id=r["post_id"],
                score=r["score"],
                content=r["content"],
            )
            for r in results
        ]
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@router.post("/similarity", response_model=list[SimilarityScore])
async def get_similarity_scores(request: SimilarityRequest):
    """Get similarity scores between a user's interests and given posts."""
    try:
        service = get_service()
        scores = service.get_similarity_scores(
            user_id=request.user_id,
            post_ids=request.post_ids,
        )
        return [
            SimilarityScore(post_id=pid, score=score)
            for pid, score in scores.items()
        ]
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@router.get("/user-vector/{user_id}")
async def get_user_vector(user_id: int):
    """Get a user's aggregated interest vector."""
    service = get_service()
    vector = service.get_user_interest_vector(user_id)

    if vector is None:
        return {"user_id": user_id, "vector": None, "message": "No interaction history"}

    return {
        "user_id": user_id,
        "dimensions": len(vector),
        "vector": vector,
    }
