# TaskStatusUpdate


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**status** | **str** | Target status following strict order transition | 

## Example

```python
from task_management_sdk.models.task_status_update import TaskStatusUpdate

# TODO update the JSON string below
json = "{}"
# create an instance of TaskStatusUpdate from a JSON string
task_status_update_instance = TaskStatusUpdate.from_json(json)
# print the JSON string representation of the object
print(TaskStatusUpdate.to_json())

# convert the object into a dict
task_status_update_dict = task_status_update_instance.to_dict()
# create an instance of TaskStatusUpdate from a dict
task_status_update_from_dict = TaskStatusUpdate.from_dict(task_status_update_dict)
```
[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


