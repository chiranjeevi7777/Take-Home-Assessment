"""
Core embedding service.

Orchestrates provider selection, embedding generation,
vector storage, and similarity search.
"""

import numpy as np

from ..config import settings
from .providers import (
    BaseEmbeddingProvider,
    OpenAIProvider,
    SentenceTransformerProvider,
)
from .vector_store import VectorStore


class EmbeddingService:
    def __init__(self):
        self._provider = self._create_provider()
        self._store = VectorStore()

    def _create_provider(self) -> BaseEmbeddingProvider:
        """Factory: create the configured embedding provider."""
        if settings.embedding_provider == "openai":
            return OpenAIProvider(
                api_key=settings.openai_api_key,
                model=settings.openai_model,
            )
        return SentenceTransformerProvider(model_name=settings.embedding_model)

    @property
    def provider(self) -> BaseEmbeddingProvider:
        return self._provider

    @property
    def store(self) -> VectorStore:
        return self._store

    # ── Embedding Generation ────────────────────────────────

    def embed_post(self, post_id: int, content: str, user_id: int | None = None) -> str:
        """
        Generate and store an embedding for a post.
        Returns the embedding_id.
        """
        embedding_id = f"post_{post_id}"
        vector = self._provider.embed(content)

        metadata = {"post_id": post_id}
        if user_id is not None:
            metadata["user_id"] = user_id

        self._store.store(
            embedding_id=embedding_id,
            vector=vector,
            metadata=metadata,
            document=content,
        )

        return embedding_id

    def embed_batch(self, items: list[dict]) -> list[str]:
        """
        Batch embed multiple posts.
        Each item: {post_id: int, content: str, user_id: int | None}
        Returns list of embedding_ids.
        """
        contents = [item["content"] for item in items]
        vectors = self._provider.embed_batch(contents)

        embedding_ids = [f"post_{item['post_id']}" for item in items]
        metadatas = [
            {
                "post_id": item["post_id"],
                **({"user_id": item["user_id"]} if item.get("user_id") else {}),
            }
            for item in items
        ]

        self._store.store_batch(
            embedding_ids=embedding_ids,
            vectors=vectors,
            metadatas=metadatas,
            documents=contents,
        )

        return embedding_ids

    # ── Similarity Search ───────────────────────────────────

    def search(self, query: str, limit: int = 20) -> list[dict]:
        """
        Semantic search: embed the query and find nearest posts.
        Returns list of {id, post_id, score, document}.
        """
        query_vector = self._provider.embed(query)
        results = self._store.query(query_vector, n_results=limit)

        return [
            {
                "post_id": item["metadata"].get("post_id"),
                "score": item["score"],
                "content": item["document"],
            }
            for item in results
            if item["metadata"].get("post_id") is not None
        ]

    def get_similarity_scores(
        self, user_id: int, post_ids: list[int]
    ) -> dict[int, float]:
        """
        Compute similarity between a user's interest vector
        and a set of post embeddings.

        The user interest vector is the mean of embeddings
        of posts the user has interacted with.
        """
        # Get user interest vector
        user_vector = self._get_user_interest_vector(user_id)
        if user_vector is None:
            # No interaction history — return default similarity
            return {pid: 0.5 for pid in post_ids}

        # Get post vectors
        post_embedding_ids = [f"post_{pid}" for pid in post_ids]
        post_vectors = self._store.get_vectors(post_embedding_ids)

        scores = {}
        for pid in post_ids:
            emb_id = f"post_{pid}"
            if emb_id in post_vectors:
                similarity = self._cosine_similarity(user_vector, post_vectors[emb_id])
                scores[pid] = round(max(0.0, similarity), 6)
            else:
                scores[pid] = 0.5  # Default for unembedded posts

        return scores

    def get_user_interest_vector(self, user_id: int) -> list[float] | None:
        """Public accessor for user interest vector."""
        return self._get_user_interest_vector(user_id)

    # ── Private Helpers ─────────────────────────────────────

    def _get_user_interest_vector(self, user_id: int) -> list[float] | None:
        """
        Compute user interest vector as the mean of embeddings
        of posts they have interacted with.

        In a production system, this would be cached/precomputed.
        """
        # Query ChromaDB for posts by this user (as proxy for interests)
        # In production, this would use actual interaction data from the main DB
        results = self._store.query(
            query_vector=[0.0] * self._provider.dimension(),  # Dummy vector
            n_results=50,
            where={"user_id": user_id},
        )

        if not results:
            return None

        # Get the actual vectors for these posts
        embedding_ids = [item["id"] for item in results]
        vectors = self._store.get_vectors(embedding_ids)

        if not vectors:
            return None

        # Mean pooling
        vector_list = list(vectors.values())
        mean_vector = np.mean(vector_list, axis=0).tolist()

        return mean_vector

    @staticmethod
    def _cosine_similarity(vec_a: list[float], vec_b: list[float]) -> float:
        """Compute cosine similarity between two vectors."""
        a = np.array(vec_a)
        b = np.array(vec_b)

        dot_product = np.dot(a, b)
        norm_a = np.linalg.norm(a)
        norm_b = np.linalg.norm(b)

        if norm_a == 0 or norm_b == 0:
            return 0.0

        return float(dot_product / (norm_a * norm_b))
