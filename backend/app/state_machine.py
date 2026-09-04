from enum import Enum

class TaskStatus(str, Enum):
    TODO = "todo"
    IN_PROGRESS = "in_progress"
    REVIEW = "review"
    DONE = "done"

# Allowed task status transitions dictionary
# Each status maps to the strictly allowed next status(es)
ALLOWED_TRANSITIONS = {
    TaskStatus.TODO: [TaskStatus.IN_PROGRESS],
    TaskStatus.IN_PROGRESS: [TaskStatus.REVIEW],
    TaskStatus.REVIEW: [TaskStatus.DONE],
    TaskStatus.DONE: []  # Final state, no transitions out
}

ORDERED_STATUS_LIST = [
    TaskStatus.TODO,
    TaskStatus.IN_PROGRESS,
    TaskStatus.REVIEW,
    TaskStatus.DONE
]

def validate_status_transition(current_status: str, new_status: str) -> None:
    """
    Validates if transitioning from current_status to new_status is allowed.
    Raises ValueError if transition breaks the required sequential order.
    """
    if current_status == new_status:
        return

    try:
        curr_enum = TaskStatus(current_status)
        new_enum = TaskStatus(new_status)
    except ValueError:
        raise ValueError(f"Invalid status value. Allowed statuses: {[s.value for s in TaskStatus]}")

    allowed = ALLOWED_TRANSITIONS.get(curr_enum, [])
    if new_enum not in allowed:
        next_expected = allowed[0].value if allowed else "None (Task is completed)"
        raise ValueError(
            f"Invalid status transition from '{current_status}' to '{new_status}'. "
            f"Tasks must follow strict order: todo -> in_progress -> review -> done. "
            f"Next allowed status for '{current_status}' is '{next_expected}'."
        )
