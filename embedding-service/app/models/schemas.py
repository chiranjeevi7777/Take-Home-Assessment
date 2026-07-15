"""
Pydantic request/response schemas for the embedding service API.
"""

from pydantic import BaseModel, Field


# ── Requests ────────────────────────────────────────────────

class EmbedRequest(BaseModel):
    post_id: int
    content: str = Field(min_length=1, max_length=10000)
    user_id: int | None = None


class EmbedBatchRequest(BaseModel):
    items: list[EmbedRequest] = Field(min_length=1, max_length=100)


class SimilarityRequest(BaseModel):
    user_id: int
    post_ids: list[int] = Field(min_length=1, max_length=200)


class SearchRequest(BaseModel):
    query: str = Field(min_length=2, max_length=500)
    limit: int = Field(default=20, ge=1, le=100)


# ── Responses ───────────────────────────────────────────────

class EmbedResponse(BaseModel):
    post_id: int
    embedding_id: str
    dimensions: int


class SimilarityScore(BaseModel):
    post_id: int
    score: float


class SearchResult(BaseModel):
    post_id: int
    score: float
    content: str | None = None


class HealthResponse(BaseModel):
    status: str
    provider: str
    dimension: int
    collection_count: int
