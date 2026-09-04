from typing import List, Optional
from fastapi import FastAPI, Depends, HTTPException, status, Query, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from sqlalchemy.orm import Session

from app.database import engine, Base, get_db
from app.schemas import TaskCreate, TaskUpdate, TaskStatusUpdate, TaskResponse
from app.crud import get_task, get_tasks, create_task, update_task, delete_task
from app.state_machine import ALLOWED_TRANSITIONS, TaskStatus, validate_status_transition

# Create tables
Base.metadata.create_all(bind=engine)

app = FastAPI(
    title="Task Management System API",
    description=(
        "FastAPI Task Management Backend with strict state-machine transition rules. "
        "Task status can only change in strict sequential order: TODO -> IN_PROGRESS -> REVIEW -> DONE."
    ),
    version="1.0.0"
)

# CORS Middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.exception_handler(ValueError)
async def value_error_exception_handler(request: Request, exc: ValueError):
    return JSONResponse(
        status_code=status.HTTP_400_BAD_REQUEST,
        content={"detail": str(exc)},
    )

@app.get("/", tags=["Health"])
def root():
    return {
        "message": "Task Management API is running",
        "docs": "/docs",
        "openapi_spec": "/openapi.json"
    }

@app.get("/api/tasks", response_model=List[TaskResponse], tags=["Tasks"])
def list_tasks(
    status: Optional[str] = Query(None, description="Filter by status: todo, in_progress, review, done"),
    search: Optional[str] = Query(None, description="Search term in title or description"),
    db: Session = Depends(get_db)
):
    """Retrieve all tasks with optional status filtering and keyword search."""
    return get_tasks(db, status=status, search=search)

@app.post("/api/tasks", response_model=TaskResponse, status_code=status.HTTP_201_CREATED, tags=["Tasks"])
def create_new_task(task: TaskCreate, db: Session = Depends(get_db)):
    """Create a new task. Tasks default to 'todo' status."""
    return create_task(db, task)

@app.get("/api/tasks/{task_id}", response_model=TaskResponse, tags=["Tasks"])
def read_task(task_id: int, db: Session = Depends(get_db)):
    """Get task details by ID."""
    task = get_task(db, task_id)
    if not task:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail=f"Task with ID {task_id} not found")
    return task

@app.put("/api/tasks/{task_id}", response_model=TaskResponse, tags=["Tasks"])
def update_task_details(task_id: int, task_in: TaskUpdate, db: Session = Depends(get_db)):
    """
    Update task details or status.
    NOTE: Status transitions are strictly enforced in order: todo -> in_progress -> review -> done.
    """
    task = get_task(db, task_id)
    if not task:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail=f"Task with ID {task_id} not found")
    return update_task(db, task, task_in)

@app.patch("/api/tasks/{task_id}/status", response_model=TaskResponse, tags=["Tasks"])
def update_task_status_only(task_id: int, status_in: TaskStatusUpdate, db: Session = Depends(get_db)):
    """
    Advance or change task status.
    NOTE: Status transitions are strictly enforced in order: todo -> in_progress -> review -> done.
    """
    task = get_task(db, task_id)
    if not task:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail=f"Task with ID {task_id} not found")
    
    task_update = TaskUpdate(status=status_in.status)
    return update_task(db, task, task_update)

@app.delete("/api/tasks/{task_id}", status_code=status.HTTP_204_NO_CONTENT, tags=["Tasks"])
def remove_task(task_id: int, db: Session = Depends(get_db)):
    """Delete a task by ID."""
    task = get_task(db, task_id)
    if not task:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail=f"Task with ID {task_id} not found")
    delete_task(db, task)
    return None

@app.get("/api/workflow/rules", tags=["Workflow"])
def get_workflow_rules():
    """Returns the task status order and transition rules."""
    return {
        "ordered_statuses": [s.value for s in TaskStatus],
        "allowed_transitions": {k.value: [v.value for v in vals] for k, vals in ALLOWED_TRANSITIONS.items()},
        "rule_description": "Tasks must strictly transition in order: todo -> in_progress -> review -> done."
    }
