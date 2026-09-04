# 🚀 Task Management System with FastAPI, SQLite, React & OpenAPI SDK

A full-stack, enterprise-grade Task Management System built with **FastAPI**, **SQLite**, **ReactJS (Vite + Tailwind CSS)**, and an automatically generated **Python SDK** via **OpenAPI Generator CLI**.

🔗 **GitHub Repository Link**: [https://github.com/chiranjeevi7777/guised-up-assessment-chirnjeevi.git](https://github.com/chiranjeevi7777/guised-up-assessment-chirnjeevi.git)

---

## 📌 Features & Key Capabilities

### 1. ⚙️ Backend API (FastAPI + SQLite)
- **RESTful Endpoints**: Full CRUD capabilities for task management.
- **SQLite Database**: Lightweight, zero-config relational persistence via SQLAlchemy 2.0 ORM.
- **Auto-Generated OpenAPI Spec**: Serves `openapi.json` and interactive Swagger docs at `/docs`.

### 2. 🎯 Trick Requirement: Strict Sequential Status Transition
The system enforces a **strict state machine** for task status progression:
$$\text{TODO} \longrightarrow \text{IN\_PROGRESS} \longrightarrow \text{REVIEW} \longrightarrow \text{DONE}$$

- Tasks start automatically at **`todo`**.
- Out-of-order state transitions (e.g., jumping from `todo` directly to `done` or `review`) are blocked by the API and return an **HTTP 400 Bad Request** error:
  ```json
  {
    "detail": "Invalid status transition from 'todo' to 'done'. Tasks must follow strict order: todo -> in_progress -> review -> done."
  }
  ```
- Reverting to prior states is disallowed once completed.

### 3. 🎨 Modern Glassmorphic Frontend (ReactJS + Vite)
- **Responsive Views**: Switch dynamically between **Kanban Board** view and **List Table** view.
- **Interactive State Transitions**: Action buttons dynamically suggest only valid next transitions according to the state machine rules.
- **Trick Verification Feature**: Includes a dedicated "Test Skip" button to intentionally trigger and demonstrate the backend state validation error in real-time.
- **Search & Filtering**: Live search by keyword and filter by status.

### 4. 🐍 OpenAPI Generated Python SDK
- Generated automatically using `@openapitools/openapi-generator-cli` from `openapi.json`.
- Located in `/python_sdk`.
- Includes a standalone verification script (`demo_sdk.py`) testing client instantiation, task creation, error handling on invalid transitions, and sequential state updates.

---

## 🛠️ Automated Setup & Execution (Batch Scripts)

The repository provides automated `.bat` scripts for setup and execution on Windows:

| Batch Script | Description |
| :--- | :--- |
| `setup.bat` | Installs backend & frontend dependencies, exports `openapi.json`, and generates the Python SDK. |
| `run_backend.bat` | Starts the FastAPI server on `http://localhost:8000`. |
| `run_frontend.bat` | Starts the React Vite dev server on `http://localhost:5173`. |
| `generate_sdk.bat` | Re-exports `openapi.json` and regenerates the Python SDK in `/python_sdk`. |
| `run_sdk_demo.bat` | Runs `demo_sdk.py` to test and validate SDK operations against the live API. |
| `run_all.bat` | Launches both backend and frontend servers simultaneously. |

---

## 💻 Manual Step-by-Step Instructions

### Step 1: Backend Setup
```bash
cd backend
python -m pip install -r requirements.txt
python export_openapi.py   # Exports openapi.json
python -m uvicorn app.main:app --reload --port 8000
```
- Interactive API Docs: `http://localhost:8000/docs`
- OpenAPI JSON Spec: `http://localhost:8000/openapi.json`

### Step 2: Generate Python SDK
```bash
npx @openapitools/openapi-generator-cli generate -i openapi.json -g python -o python_sdk --additional-properties=packageName=task_management_sdk
```

### Step 3: Run SDK Demo
```bash
python demo_sdk.py
```

### Step 4: Frontend Setup
```bash
cd frontend
npm install
npm run dev
```
Access the UI at `http://localhost:5173`.

---

## 🧪 Testing

Run backend unit and integration tests (including trick requirement validation):
```bash
cd backend
pytest test_main.py
```

---

## 📊 API Reference Summary

- `GET /api/tasks` - List tasks (supports `status` and `search` query parameters)
- `POST /api/tasks` - Create task (defaults to `todo`)
- `GET /api/tasks/{task_id}` - Get task details
- `PUT /api/tasks/{task_id}` - Update task details / status (enforces strict order)
- `PATCH /api/tasks/{task_id}/status` - Advance task status (enforces strict order)
- `DELETE /api/tasks/{task_id}` - Delete task
- `GET /api/workflow/rules` - Get active state transition rules
