# Interactive Walkthrough & Demo Guide

Follow these steps to demonstrate the full capabilities of Guised Up.

---

## 1. Automated Seeder Validation

Verify that your system seeded correctly by inspecting user profiles and authenticity ratings:

- **Seeded Creators**: User IDs 1 through 5 are seeded with specific authenticity ratings.
- **Interactions**: User 1 has followed User 2, and reacted to their posts.
- **Posts**: Initial posts are computed with authenticity scores based on content criteria.

---

## 2. API Endpoint Testing via `curl`

Verify endpoints and Sanctum token authentication.

### Step 2.1: Register a new user
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name": "Alice", "email": "alice@example.com", "password": "Password123", "password_confirmation": "Password123"}'
```
*Note the returned token in the response: `"token": "1|abc..."`.*

### Step 2.2: Fetch Feed (Authenticated)
Replace `YOUR_TOKEN_HERE` with the token received from the previous step:
```bash
curl -X GET http://127.0.0.1:8000/api/feed \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```
*Verify that the returned posts display calculated `authenticity_score` and `ranking_score` matching the formula.*

---

## 3. Simulating Embedding Service Fallback

Test the resilience of the Laravel application to python service outages:

1. **Stop the Python Service**: Shut down the running `uvicorn` instance.
2. **Retrieve Feed**: Call the `/api/feed` endpoint again.
   - Verify that the feed still loads correctly (the Laravel backend falls back to $0.5$ for similarity scores).
3. **Execute Search**: Call the search endpoint:
   ```bash
   curl -X GET "http://127.0.0.1:8000/api/search?q=genuine" \
     -H "Authorization: Bearer YOUR_TOKEN_HERE" \
     -H "Accept: application/json"
   ```
   - Verify that the results return successfully (the backend gracefully switched from ChromaDB vector retrieval to database SQL `LIKE` filtering).
4. **Inspect Backend Logs**: Open `backend/storage/logs/laravel.log` and verify the fallback warning notices:
   ```text
   [2026-07-15] local.WARNING: Embedding service offline. Falling back to LIKE search.
   ```
