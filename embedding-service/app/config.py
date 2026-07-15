"""
Application configuration via environment variables.
"""

from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    # ── Embedding Provider ──────────────────────────────────
    embedding_provider: str = "sentence_transformer"  # or "openai"
    embedding_model: str = "all-MiniLM-L6-v2"
    embedding_dimension: int = 384

    # ── OpenAI (optional) ───────────────────────────────────
    openai_api_key: str = ""
    openai_model: str = "text-embedding-3-small"

    # ── ChromaDB ────────────────────────────────────────────
    chroma_persist_dir: str = "./chroma_data"
    chroma_collection_name: str = "posts"

    # ── Server ──────────────────────────────────────────────
    host: str = "0.0.0.0"
    port: int = 8001
    debug: bool = True

    model_config = {"env_file": ".env", "env_prefix": "EMB_"}


settings = Settings()
