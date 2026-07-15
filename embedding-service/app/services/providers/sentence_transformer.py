"""
SentenceTransformer embedding provider.

Uses the all-MiniLM-L6-v2 model (384 dimensions) by default.
Runs entirely locally — no API keys, no network calls.
"""

from sentence_transformers import SentenceTransformer as ST

from .base import BaseEmbeddingProvider


class SentenceTransformerProvider(BaseEmbeddingProvider):
    def __init__(self, model_name: str = "all-MiniLM-L6-v2"):
        self._model_name = model_name
        try:
            self._model = ST(model_name)
            self._dimension = self._model.get_sentence_embedding_dimension()
            self._is_mock = False
        except Exception as e:
            print(f"Failed to load sentence-transformers model '{model_name}': {e}. Falling back to mock generator.")
            self._model = None
            self._dimension = 384
            self._is_mock = True

    def embed(self, text: str) -> list[float]:
        if self._is_mock:
            import hashlib
            import random
            # Seed the RNG deterministically based on content hash
            h = int(hashlib.md5(text.encode('utf-8')).hexdigest(), 16)
            rng = random.Random(h)
            vec = [rng.uniform(-1.0, 1.0) for _ in range(self._dimension)]
            norm = sum(x**2 for x in vec) ** 0.5
            return [x / norm for x in vec] if norm > 0 else vec

        embedding = self._model.encode(text, normalize_embeddings=True)
        return embedding.tolist()

    def embed_batch(self, texts: list[str]) -> list[list[float]]:
        if self._is_mock:
            return [self.embed(t) for t in texts]
        embeddings = self._model.encode(texts, normalize_embeddings=True)
        return embeddings.tolist()

    def dimension(self) -> int:
        return self._dimension

    @property
    def name(self) -> str:
        return f"sentence_transformer/{self._model_name}" + (" (mocked)" if self._is_mock else "")
