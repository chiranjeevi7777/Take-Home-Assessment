@echo off
echo Starting FastAPI Task Management Server on http://localhost:8000 ...
cd backend
python -m uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
