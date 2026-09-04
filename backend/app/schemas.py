from datetime import datetime
from typing import Optional
from pydantic import BaseModel, Field, ConfigDict
from app.state_machine import TaskStatus

class TaskCreate(BaseModel):
    title: str = Field(..., min_length=1, max_length=255, description="Title of the task")
    description: Optional[str] = Field(None, description="Detailed task description")
    priority: Optional[str] = Field("medium", description="Priority level: low, medium, or high")
    due_date: Optional[str] = Field(None, description="Due date string e.g. YYYY-MM-DD")

class TaskUpdate(BaseModel):
    title: Optional[str] = Field(None, min_length=1, max_length=255)
    description: Optional[str] = None
    status: Optional[str] = Field(None, description="New status following strict order: todo -> in_progress -> review -> done")
    priority: Optional[str] = None
    due_date: Optional[str] = None

class TaskStatusUpdate(BaseModel):
    status: str = Field(..., description="Target status following strict order transition")

class TaskResponse(BaseModel):
    id: int
    title: str
    description: Optional[str] = None
    status: str
    priority: str
    due_date: Optional[str] = None
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)
