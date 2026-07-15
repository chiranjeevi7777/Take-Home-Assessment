"""
Embedding API routes.

Handles embedding generation and storage operations.
"""

from fastapi import APIRouter, HTTPException

from ..models.schemas import EmbedBatchRequest, EmbedRequest, EmbedResponse
from ..services.embedding_service import EmbeddingService

router = APIRouter(tags=["embeddings"])

# Singleton service instance — initialized on first import
_service: EmbeddingService | None = None


def get_service() -> EmbeddingService:
    global _service
    if _service is None:
        _service = EmbeddingService()
    return _service


@router.post("/embed", response_model=EmbedResponse)
async def embed_post(request: EmbedRequest):
    """Generate and store an embedding for a single post."""
    try:
        service = get_service()
        embedding_id = service.embed_post(
            post_id=request.post_id,
            content=request.content,
            user_id=request.user_id,
        )
        return EmbedResponse(
            post_id=request.post_id,
            embedding_id=embedding_id,
            dimensions=service.provider.dimension(),
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@router.post("/embed-batch", response_model=list[EmbedResponse])
async def embed_batch(request: EmbedBatchRequest):
    """Batch embed multiple posts."""
    try:
        service = get_service()
        items = [item.model_dump() for item in request.items]
        embedding_ids = service.embed_batch(items)

        return [
            EmbedResponse(
                post_id=item.post_id,
                embedding_id=eid,
                dimensions=service.provider.dimension(),
            )
            for item, eid in zip(request.items, embedding_ids)
        ]
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
