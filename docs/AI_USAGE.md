# AI Usage Documentation — Guised Up

In compliance with the AI transparency policies of the assessment, this document records the engineering issues addressed with AI coding assistants, objectives, human revisions, and architectural justifications.

---

## 1. Context

AI-assisted generation was leveraged to accelerate boilerplate creation, standard design patterns, and cross-language communication abstractions.

---

## 2. Engineering Logs

### Log 2.1: Laravel Custom Feed Scoring
- **Problem**: Decouple multi-signal scoring formulas from database models to prevent code bloat.
- **Prompt Objective**: Scaffold a Laravel Service Container pattern implementingcomposed scorers (`Authenticity`, `Relationship`, `Similarity`, `Recency`) based on a single weight configuration matrix.
- **Generated Output**: A unified `RankingService` class injecting individual scorer classes.
- **Human Revisions**:
  - Implemented the SentenceTransformer interface as the standard for similarity fallback check.
  - Added clamping bounds ($[0.0, 1.0]$) to all computed values to prevent mathematical overflow during sum calculations.
- **Engineering Decision**: Isolating each signal into its own directory (`app/Scoring/`) ensures easy expansion if other signals (e.g. verified badge, reporting flags) are added later.

### Log 2.2: Python-PHP Interop Client
- **Problem**: Implement clean HTTP queries to request text embeddings from FastAPI without locking application threads.
- **Prompt Objective**: Create an HTTP-based `EmbeddingClient` in Laravel using `Illuminate\Support\Facades\Http` that wraps batch requests and registers timeout durations.
- **Generated Output**: A basic Guzzle client wrapper class.
- **Human Revisions**:
  - Encapsulated connection handling inside a `try-catch` block returning nulls on connection timeouts.
  - Implemented automatic logging to allow operations metrics reporting.
- **Engineering Decision**: Decoupling the client from specific URLs makes swapping the embedding engine (e.g. moving from FastAPI/SentenceTransformers to OpenAI's Cloud API) simple.

### Log 2.3: Mobile Optimistic Reactions
- **Problem**: Clicking a reaction button has lag if the device waits for API response.
- **Prompt Objective**: Draft a custom React hook in React Native that optimistic-updates like statuses, counts, and reactions, using a temporary local state, while executing the API request in the background.
- **Generated Output**: A state machine rendering react events.
- **Human Revisions**:
  - Integrated debounce logic to prevent double-click requests from spamming the database.
  - Re-bound native feedback events to trigger on touch rather than on promise completion.
- **Engineering Decision**: Prioritizing local feedback optimizes perceived user experience on mobile screens.
