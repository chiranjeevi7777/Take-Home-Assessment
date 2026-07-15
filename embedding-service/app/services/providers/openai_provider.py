"""
OpenAI embedding provider.

Uses text-embedding-3-small (1536 dimensions) by default.
Requires OPENAI_API_KEY environment variable.
Demonstrates the interchangeable provider pattern.
"""

import httpx

from .base import BaseEmbeddingProvider


class OpenAIProvider(BaseEmbeddingProvider):
    API_URL = "https://api.openai.com/v1/embeddings"

    def __init__(self, api_key: str, model: str = "text-embedding-3-small"):
        if not api_key:
            raise ValueError("OpenAI API key is required")
        self._api_key = api_key
        self._model = model
        # Dimension lookup for known models
        self._dimensions = {
            "text-embedding-3-small": 1536,
            "text-embedding-3-large": 3072,
            "text-embedding-ada-002": 1536,
        }

    def embed(self, text: str) -> list[float]:
        return self.embed_batch([text])[0]

    def embed_batch(self, texts: list[str]) -> list[list[float]]:
        response = httpx.post(
            self.API_URL,
            headers={
                "Authorization": f"Bearer {self._api_key}",
                "Content-Type": "application/json",
            },
            json={"input": texts, "model": self._model},
            timeout=30.0,
        )
        response.raise_for_status()
        data = response.json()

        # Sort by index to maintain order
        sorted_embeddings = sorted(data["data"], key=lambda x: x["index"])
        return [item["embedding"] for item in sorted_embeddings]

    def dimension(self) -> int:
        return self._dimensions.get(self._model, 1536)

    @property
    def name(self) -> str:
        return f"openai/{self._model}"
