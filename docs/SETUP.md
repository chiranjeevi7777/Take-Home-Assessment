# Local Installation & Setup Guide

This document describes how to set up the Guised Up full-stack stack locally.

---

## Prerequisites

Ensure you have the following installed on your developer machine:
- **PHP** (8.2+ with `sqlite3` extension)
- **Composer** (PHP Package Manager)
- **Node.js** (20+) & **npm**
- **Python** (3.10+) & `pip`

---

## 1. Application Backend (Laravel)

1. Navigate to the `backend/` directory:
   ```bash
   cd backend
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Configure environment variables:
   ```bash
   cp .env.example .env
   ```
   *Ensure `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite` are set.*

4. Initialize the SQLite database and seed the system with mock users, authentic creators, relationships, and posts:
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

5. Start the local server:
   ```bash
   php artisan serve --port=8000
   ```

---

## 2. Python Embedding Service (FastAPI)

1. Navigate to the `embedding-service/` directory:
   ```bash
   cd embedding-service
   ```

2. Create and activate a Python virtual environment:
   ```bash
   python -m venv venv
   source venv/bin/activate  # On Windows: .\venv\Scripts\activate
   ```

3. Install required packages (SentenceTransformers, FastAPI, ChromaDB):
   ```bash
   pip install -r requirements.txt
   ```

4. Start the service:
   ```bash
   uvicorn app.main:app --host 127.0.0.1 --port 8080 --reload
   ```

---

## 3. Mobile Frontend (React Native / Expo)

1. Navigate to the `mobile/` directory:
   ```bash
   cd mobile
   ```

2. Install packages:
   ```bash
   npm install
   ```

3. Configure connection endpoints:
   - Edit the API client URL in `src/services/api.js` (or via `.env` / `config.js` pointing to your local Laravel server: `http://127.0.0.1:8000/api` or machine IP).

4. Start the Expo development server:
   ```bash
   npx expo start
   ```
