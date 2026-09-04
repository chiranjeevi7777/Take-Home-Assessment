from typing import List, Optional
from sqlalchemy.orm import Session
from datetime import datetime
from app.models import Task
from app.schemas import TaskCreate, TaskUpdate
from app.state_machine import validate_status_transition

def get_task(db: Session, task_id: int) -> Optional[Task]:
    return db.query(Task).filter(Task.id == task_id).first()

def get_tasks(db: Session, status: Optional[str] = None, search: Optional[str] = None) -> List[Task]:
    query = db.query(Task)
    if status:
        query = query.filter(Task.status == status)
    if search:
        query = query.filter(
            (Task.title.ilike(f"%{search}%")) | (Task.description.ilike(f"%{search}%"))
        )
    return query.order_by(Task.id.desc()).all()

def create_task(db: Session, task_in: TaskCreate) -> Task:
    db_task = Task(
        title=task_in.title,
        description=task_in.description,
        priority=task_in.priority or "medium",
        due_date=task_in.due_date,
        status="todo"  # Every task starts at 'todo'
    )
    db.add(db_task)
    db.commit()
    db.refresh(db_task)
    return db_task

def update_task(db: Session, db_task: Task, task_in: TaskUpdate) -> Task:
    update_data = task_in.model_dump(exclude_unset=True)

    # Check status transition rule if status update is requested
    if "status" in update_data and update_data["status"] is not None:
        new_status = update_data["status"]
        validate_status_transition(db_task.status, new_status)
        db_task.status = new_status

    if "title" in update_data and update_data["title"] is not None:
        db_task.title = update_data["title"]
    if "description" in update_data and update_data["description"] is not None:
        db_task.description = update_data["description"]
    if "priority" in update_data and update_data["priority"] is not None:
        db_task.priority = update_data["priority"]
    if "due_date" in update_data and update_data["due_date"] is not None:
        db_task.due_date = update_data["due_date"]

    db_task.updated_at = datetime.utcnow()
    db.commit()
    db.refresh(db_task)
    return db_task

def delete_task(db: Session, db_task: Task) -> bool:
    db.delete(db_task)
    db.commit()
    return True
