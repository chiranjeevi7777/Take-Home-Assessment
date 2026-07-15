"""
Abstract base class for embedding providers.

All providers must implement embed() and embed_batch().
This enables swapping between SentenceTransformer, OpenAI,
or any future provider without changing consumer code.
"""

from abc import ABC, abstractmethod


class BaseEmbeddingProvider(ABC):
    """Interface for all embedding providers."""

    @abstractmethod
    def embed(self, text: str) -> list[float]:
        """Generate an embedding vector for a single text."""
        ...

    @abstractmethod
    def embed_batch(self, texts: list[str]) -> list[list[float]]:
        """Generate embedding vectors for multiple texts."""
        ...

    @abstractmethod
    def dimension(self) -> int:
        """Return the dimension of embedding vectors produced."""
        ...

    @property
    @abstractmethod
    def name(self) -> str:
        """Return the provider name for logging/health checks."""
        ...
