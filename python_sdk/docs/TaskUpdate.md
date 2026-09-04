# TaskUpdate


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**title** | **str** |  | [optional] 
**description** | **str** |  | [optional] 
**status** | **str** | New status following strict order: todo -&gt; in_progress -&gt; review -&gt; done | [optional] 
**priority** | **str** |  | [optional] 
**due_date** | **str** |  | [optional] 

## Example

```python
from task_management_sdk.models.task_update import TaskUpdate

# TODO update the JSON string below
json = "{}"
# create an instance of TaskUpdate from a JSON string
task_update_instance = TaskUpdate.from_json(json)
# print the JSON string representation of the object
print(TaskUpdate.to_json())

# convert the object into a dict
task_update_dict = task_update_instance.to_dict()
# create an instance of TaskUpdate from a dict
task_update_from_dict = TaskUpdate.from_dict(task_update_dict)
```
[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


