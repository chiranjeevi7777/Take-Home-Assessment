# TaskCreate


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**title** | **str** | Title of the task | 
**description** | **str** | Detailed task description | [optional] 
**priority** | **str** | Priority level: low, medium, or high | [optional] 
**due_date** | **str** | Due date string e.g. YYYY-MM-DD | [optional] 

## Example

```python
from task_management_sdk.models.task_create import TaskCreate

# TODO update the JSON string below
json = "{}"
# create an instance of TaskCreate from a JSON string
task_create_instance = TaskCreate.from_json(json)
# print the JSON string representation of the object
print(TaskCreate.to_json())

# convert the object into a dict
task_create_dict = task_create_instance.to_dict()
# create an instance of TaskCreate from a dict
task_create_from_dict = TaskCreate.from_dict(task_create_dict)
```
[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


