"""
ChromaDB vector store wrapper.

Handles all vector persistence and retrieval operations.
Abstracted to allow swapping to Pinecone, Weaviate, etc.
"""

import chromadb
from chromadb.config import Settings as ChromaSettings

from ..config import settings


class VectorStore:
    def __init__(self):
        self._client = chromadb.Client(
            ChromaSettings(
                persist_directory=settings.chroma_persist_dir,
                anonymized_telemetry=False,
            )
        )
        self._collection = self._client.get_or_create_collection(
            name=settings.chroma_collection_name,
            metadata={"hnsw:space": "cosine"},
        )

    @property
    def count(self) -> int:
        return self._collection.count()

    def store(
        self,
        embedding_id: str,
        vector: list[float],
        metadata: dict | None = None,
        document: str | None = None,
    ) -> None:
        """Store a single embedding with metadata."""
        self._collection.upsert(
            ids=[embedding_id],
            embeddings=[vector],
            metadatas=[metadata] if metadata else None,
            documents=[document] if document else None,
        )

    def store_batch(
        self,
        embedding_ids: list[str],
        vectors: list[list[float]],
        metadatas: list[dict] | None = None,
        documents: list[str] | None = None,
    ) -> None:
        """Store multiple embeddings in a single operation."""
        self._collection.upsert(
            ids=embedding_ids,
            embeddings=vectors,
            metadatas=metadatas,
            documents=documents,
        )

    def query(
        self,
        query_vector: list[float],
        n_results: int = 20,
        where: dict | None = None,
    ) -> list[dict]:
        """
        Find nearest neighbors to a query vector.

        Returns list of {id, score, metadata, document}.
        """
        results = self._collection.query(
            query_embeddings=[query_vector],
            n_results=n_results,
            where=where,
            include=["metadatas", "documents", "distances"],
        )

        items = []
        if results and results["ids"] and results["ids"][0]:
            for i, emb_id in enumerate(results["ids"][0]):
                # ChromaDB returns distances; convert to similarity
                # For cosine space: similarity = 1 - distance
                distance = results["distances"][0][i] if results["distances"] else 0
                items.append(
                    {
                        "id": emb_id,
                        "score": round(1.0 - distance, 6),
                        "metadata": (
                            results["metadatas"][0][i]
                            if results["metadatas"]
                            else {}
                        ),
                        "document": (
                            results["documents"][0][i]
                            if results["documents"]
                            else None
                        ),
                    }
                )

        return items

    def get_vectors(self, embedding_ids: list[str]) -> dict[str, list[float]]:
        """Retrieve raw vectors by ID."""
        if not embedding_ids:
            return {}

        results = self._collection.get(
            ids=embedding_ids,
            include=["embeddings"],
        )

        vectors = {}
        if results and results["ids"]:
            for i, emb_id in enumerate(results["ids"]):
                vectors[emb_id] = results["embeddings"][i]

        return vectors

    def delete(self, embedding_ids: list[str]) -> None:
        """Remove embeddings by ID."""
        self._collection.delete(ids=embedding_ids)
