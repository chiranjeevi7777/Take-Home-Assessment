"""
Guised Up — Embedding Service

FastAPI application for generating, storing, and querying
vector embeddings for the Guised Up social platform.
"""

from fastapi import FastAPI

from .config import settings
from .models.schemas import HealthResponse
from .routers import embeddings, search

app = FastAPI(
    title="Guised Up Embedding Service",
    description="Vector embedding generation, storage, and semantic search",
    version="1.0.0",
)

# ── Routers ─────────────────────────────────────────────────
app.include_router(embeddings.router)
app.include_router(search.router)


# ── Health Check ────────────────────────────────────────────
@app.get("/health", response_model=HealthResponse)
async def health_check():
    """Service health and configuration status."""
    service = embeddings.get_service()
    return HealthResponse(
        status="healthy",
        provider=service.provider.name,
        dimension=service.provider.dimension(),
        collection_count=service.store.count,
    )


if __name__ == "__main__":
    import uvicorn

    uvicorn.run(
        "app.main:app",
        host=settings.host,
        port=settings.port,
        reload=settings.debug,
    )
