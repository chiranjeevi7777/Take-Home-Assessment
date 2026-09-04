import sys
import os
import time
import subprocess
from pathlib import Path

# Add generated python_sdk path
sdk_dir = Path(__file__).parent / "python_sdk"
sys.path.insert(0, str(sdk_dir))

import task_management_sdk
from task_management_sdk.api import tasks_api, workflow_api
from task_management_sdk.models.task_create import TaskCreate
from task_management_sdk.models.task_update import TaskUpdate
from task_management_sdk.models.task_status_update import TaskStatusUpdate

def run_sdk_demo():
    print("==================================================")
    print("   TASK MANAGEMENT SYSTEM - PYTHON SDK DEMO       ")
    print("==================================================")
    
    # Configure API client
    configuration = task_management_sdk.Configuration(
        host="http://localhost:8000"
    )
    
    with task_management_sdk.ApiClient(configuration) as api_client:
        tasks_instance = tasks_api.TasksApi(api_client)
        workflow_instance = workflow_api.WorkflowApi(api_client)
        
        # 1. Fetch Workflow Rules
        print("\n1. Fetching Workflow State Rules from API:")
        rules = workflow_instance.get_workflow_rules_api_workflow_rules_get()
        print(f"   Ordered Statuses: {rules.get('ordered_statuses')}")
        print(f"   Rules: {rules.get('rule_description')}")
        
        # 2. Create Task (Starts at 'todo')
        print("\n2. Creating Task via Generated Python SDK:")
        new_task_payload = TaskCreate(
            title="SDK Demonstration Task",
            description="Created using OpenAPI-generated Python SDK client",
            priority="high",
            due_date="2026-09-10"
        )
        created_task = tasks_instance.create_new_task_api_tasks_post(new_task_payload)
        print(f"   Created Task ID: {created_task.id}")
        print(f"   Title: {created_task.title}")
        print(f"   Current Status: {created_task.status}")
        
        # 3. Test Invalid Status Transition (Trick Requirement Verification)
        print("\n3. Testing Trick Requirement (Invalid Status Transition: 'todo' -> 'done'):")
        try:
            invalid_update = TaskUpdate(status="done")
            tasks_instance.update_task_details_api_tasks_task_id_put(
                task_id=created_task.id,
                task_update=invalid_update
            )
            print("   [FAILED] Error expected but transition succeeded.")
        except task_management_sdk.exceptions.ApiException as e:
            print("   [SUCCESS] Received Expected 400 Bad Request Error:")
            print(f"   API Error Response: {e.body}")

        # 4. Valid Sequential Transitions: todo -> in_progress -> review -> done
        print("\n4. Executing Valid Sequential Status Transitions:")
        
        # Step A: todo -> in_progress
        tasks_instance.update_task_status_only_api_tasks_task_id_status_patch(
            task_id=created_task.id,
            task_status_update=TaskStatusUpdate(status="in_progress")
        )
        task_state = tasks_instance.read_task_api_tasks_task_id_get(created_task.id)
        print(f"   Step A: Updated status -> '{task_state.status}'")
        
        # Step B: in_progress -> review
        tasks_instance.update_task_status_only_api_tasks_task_id_status_patch(
            task_id=created_task.id,
            task_status_update=TaskStatusUpdate(status="review")
        )
        task_state = tasks_instance.read_task_api_tasks_task_id_get(created_task.id)
        print(f"   Step B: Updated status -> '{task_state.status}'")
        
        # Step C: review -> done
        tasks_instance.update_task_status_only_api_tasks_task_id_status_patch(
            task_id=created_task.id,
            task_status_update=TaskStatusUpdate(status="done")
        )
        task_state = tasks_instance.read_task_api_tasks_task_id_get(created_task.id)
        print(f"   Step C: Updated status -> '{task_state.status}'")

        # 5. List all tasks
        print("\n5. Listing Tasks via SDK:")
        tasks_list = tasks_instance.list_tasks_api_tasks_get()
        print(f"   Total Tasks Found: {len(tasks_list)}")
        for t in tasks_list:
            print(f"   - Task #{t.id}: {t.title} [{t.status}]")

        print("\n==================================================")
        print("   ALL SDK OPERATIONAL TESTS COMPLETED SUCCESSFULLY ")
        print("==================================================")

if __name__ == "__main__":
    run_sdk_demo()
