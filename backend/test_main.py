import pytest
from fastapi.testclient import TestClient
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

from app.database import Base, get_db
from app.main import app

SQLALCHEMY_DATABASE_URL = "sqlite:///./test.db"
engine = create_engine(SQLALCHEMY_DATABASE_URL, connect_args={"check_same_thread": False})
TestingSessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

def override_get_db():
    try:
        db = TestingSessionLocal()
        yield db
    finally:
        db.close()

app.dependency_overrides[get_db] = override_get_db

@pytest.fixture(autouse=True)
def setup_db():
    Base.metadata.create_all(bind=engine)
    yield
    Base.metadata.drop_all(bind=engine)

client = TestClient(app)

def test_create_task():
    response = client.post("/api/tasks", json={
        "title": "Test Task 1",
        "description": "Task description",
        "priority": "high"
    })
    assert response.status_code == 201
    data = response.json()
    assert data["title"] == "Test Task 1"
    assert data["status"] == "todo"
    assert data["priority"] == "high"

def test_valid_status_transition_workflow():
    # 1. Create task (starts at todo)
    resp = client.post("/api/tasks", json={"title": "Workflow Task"})
    task_id = resp.json()["id"]
    assert resp.json()["status"] == "todo"

    # 2. Transition todo -> in_progress (VALID)
    resp = client.patch(f"/api/tasks/{task_id}/status", json={"status": "in_progress"})
    assert resp.status_code == 200
    assert resp.json()["status"] == "in_progress"

    # 3. Transition in_progress -> review (VALID)
    resp = client.patch(f"/api/tasks/{task_id}/status", json={"status": "review"})
    assert resp.status_code == 200
    assert resp.json()["status"] == "review"

    # 4. Transition review -> done (VALID)
    resp = client.patch(f"/api/tasks/{task_id}/status", json={"status": "done"})
    assert resp.status_code == 200
    assert resp.json()["status"] == "done"

def test_invalid_status_transition_tricks():
    # Create task
    resp = client.post("/api/tasks", json={"title": "Trick Task"})
    task_id = resp.json()["id"]
    assert resp.json()["status"] == "todo"

    # TRICK 1: Try skipping directly from todo -> done (INVALID)
    resp = client.patch(f"/api/tasks/{task_id}/status", json={"status": "done"})
    assert resp.status_code == 400
    assert "Invalid status transition" in resp.json()["detail"]

    # TRICK 2: Try skipping from todo -> review (INVALID)
    resp = client.patch(f"/api/tasks/{task_id}/status", json={"status": "review"})
    assert resp.status_code == 400
    assert "Invalid status transition" in resp.json()["detail"]

    # Advance to in_progress legally
    client.patch(f"/api/tasks/{task_id}/status", json={"status": "in_progress"})

    # TRICK 3: Try jumping back in_progress -> todo (INVALID)
    resp = client.patch(f"/api/tasks/{task_id}/status", json={"status": "todo"})
    assert resp.status_code == 400

def test_crud_and_filtering():
    client.post("/api/tasks", json={"title": "Task A", "priority": "low"})
    client.post("/api/tasks", json={"title": "Task B", "priority": "high"})

    # List tasks
    resp = client.get("/api/tasks")
    assert resp.status_code == 200
    assert len(resp.json()) == 2

    # Filter tasks by search
    resp = client.get("/api/tasks?search=Task A")
    assert resp.status_code == 200
    assert len(resp.json()) == 1
    assert resp.json()[0]["title"] == "Task A"
