# Guised Up — Authenticity-First Social Media Platform

Guised Up is a full-stack, authenticity-first social media application. Unlike traditional social media platforms that optimize purely for viral engagement, Guised Up uses a multi-signal AI-powered ranking engine to surface authentic posts, strengthen genuine relationships, and prioritize content quality.

---

## Repository Structure

```text
guised-up/
├── backend/                    # Laravel API (PHP 8.2+)
│   ├── app/
│   │   ├── Controllers/        # Auth, Feed, Post, Search, Interaction
│   │   ├── Models/             # User, Post, Interaction, Relationship
│   │   ├── Services/           # FeedService, RankingService, SearchService
│   │   ├── Contracts/          # RankingStrategy, EmbeddingProvider
│   │   └── Scoring/            # Isolated signal scorers (Authenticity, Recency, Relationship, Similarity)
│   ├── config/                 # Configurable weights (ranking.php, embedding.php)
│   ├── database/               # Migrations, Seeders, Factories
│   └── tests/                  # Unit and Feature tests (PHPUnit)
│
├── embedding-service/          # Python Vector Embedding Service (FastAPI)
│   ├── app/
│   │   ├── services/           # Provider abstraction & ChromaDB wrappers
│   │   └── routers/            # Search & Embedding HTTP handlers
│   └── requirements.txt        # Pinned dependencies
│
├── mobile/                     # React Native Client (Expo)
│   ├── src/
│   │   ├── components/         # Memoized UI components (PostCard, SearchBar, etc.)
│   │   ├── hooks/              # Custom React hooks (useFeed, useSearch, useReaction)
│   │   ├── services/           # api.js client wrapper
│   │   └── styles/             # theme.js design tokens
│   └── App.jsx                 # Mobile app entry point
│
├── sql/                        # SQL Assignment Queries
│   ├── D1.sql                  # Joins & Aggregation (User metrics)
│   ├── D2.sql                  # Self-Join (Mutual relationships)
│   ├── D3.sql                  # Subquery in HAVING (Authentic creators)
│   └── D4.sql                  # CTE & Window Function (Top 3 posts per user)
│
└── docs/                       # System Documentation
    ├── ARCHITECTURE.md         # Detailed architectural layout & data flows
    ├── SETUP.md                # Local installation and configuration guides
    ├── DEMO.md                 # Interactive walkthrough & script
    └── AI_USAGE.md             # AI coding assistant logs & modifications
```

---

## Core Features & Core Architecture

1. **Authenticity Scoring**: Analyzes content heuristics (length, word diversity, shouting penalties) and combines them with user authenticity scores.
2. **Personalized Feed Ranking**: Deterministic multi-signal weighted sum:
   $$\text{Score} = 0.30 \times \text{Authenticity} + 0.25 \times \text{Relationship} + 0.25 \times \text{Similarity} + 0.20 \times \text{Recency}$$
3. **Semantic Similarity & Fallbacks**: Uses a Python helper service (FastAPI + local SentenceTransformers) for vector extraction, falling back to database query filters if the vector service goes offline.
4. **Optimistic UI Updates**: Custom mobile React hooks perform instant UI changes for likes/reactions, rollback gracefully upon network failure, and debounce searches to minimize API hits.

---

## Detailed Documentation Guides

Please refer to the following documentation files located in the `docs/` directory:

- **[Technical Solution Document (TSD)](docs/TSD.md)**: Details data models, feed scoring mechanics, API endpoints, vector storage, and risk mitigations.
- **[Installation and Setup Guide](docs/SETUP.md)**: Steps to spin up the Laravel API, Python FastAPI service, and React Native client locally.
- **[System Architecture Document](docs/ARCHITECTURE.md)**: Deep dive into class structures, databases, index strategies, and sequence diagrams.
- **[Interactive Demo Script](docs/DEMO.md)**: Pre-configured API curl commands, database seeding instructions, and UI validation steps.
- **[AI Usage Log](docs/AI_USAGE.md)**: Transcripts and engineering justifications for code generated or refactored with the assistance of AI during development.

---

## SQL Queries

All SQL query solutions are located in the `sql/` directory:
- Combined file: **[sql/queries.sql](sql/queries.sql)**
- Individual query files: `D1.sql` (Joins/Aggregation), `D2.sql` (Self-Joins), `D3.sql` (Subqueries in HAVING), and `D4.sql` (CTEs/Window Functions).

