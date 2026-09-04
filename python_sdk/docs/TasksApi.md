# task_management_sdk.TasksApi

All URIs are relative to *http://localhost*

Method | HTTP request | Description
------------- | ------------- | -------------
[**create_new_task_api_tasks_post**](TasksApi.md#create_new_task_api_tasks_post) | **POST** /api/tasks | Create New Task
[**list_tasks_api_tasks_get**](TasksApi.md#list_tasks_api_tasks_get) | **GET** /api/tasks | List Tasks
[**read_task_api_tasks_task_id_get**](TasksApi.md#read_task_api_tasks_task_id_get) | **GET** /api/tasks/{task_id} | Read Task
[**remove_task_api_tasks_task_id_delete**](TasksApi.md#remove_task_api_tasks_task_id_delete) | **DELETE** /api/tasks/{task_id} | Remove Task
[**update_task_details_api_tasks_task_id_put**](TasksApi.md#update_task_details_api_tasks_task_id_put) | **PUT** /api/tasks/{task_id} | Update Task Details
[**update_task_status_only_api_tasks_task_id_status_patch**](TasksApi.md#update_task_status_only_api_tasks_task_id_status_patch) | **PATCH** /api/tasks/{task_id}/status | Update Task Status Only


# **create_new_task_api_tasks_post**
> TaskResponse create_new_task_api_tasks_post(task_create)

Create New Task

Create a new task. Tasks default to 'todo' status.

### Example


```python
import task_management_sdk
from task_management_sdk.models.task_create import TaskCreate
from task_management_sdk.models.task_response import TaskResponse
from task_management_sdk.rest import ApiException
from pprint import pprint

# Defining the host is optional and defaults to http://localhost
# See configuration.py for a list of all supported configuration parameters.
configuration = task_management_sdk.Configuration(
    host = "http://localhost"
)


# Enter a context with an instance of the API client
with task_management_sdk.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = task_management_sdk.TasksApi(api_client)
    task_create = task_management_sdk.TaskCreate() # TaskCreate | 

    try:
        # Create New Task
        api_response = api_instance.create_new_task_api_tasks_post(task_create)
        print("The response of TasksApi->create_new_task_api_tasks_post:\n")
        pprint(api_response)
    except Exception as e:
        print("Exception when calling TasksApi->create_new_task_api_tasks_post: %s\n" % e)
```



### Parameters


Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **task_create** | [**TaskCreate**](TaskCreate.md)|  | 

### Return type

[**TaskResponse**](TaskResponse.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
|-------------|-------------|------------------|
**201** | Successful Response |  -  |
**422** | Validation Error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **list_tasks_api_tasks_get**
> List[TaskResponse] list_tasks_api_tasks_get(status=status, search=search)

List Tasks

Retrieve all tasks with optional status filtering and keyword search.

### Example


```python
import task_management_sdk
from task_management_sdk.models.task_response import TaskResponse
from task_management_sdk.rest import ApiException
from pprint import pprint

# Defining the host is optional and defaults to http://localhost
# See configuration.py for a list of all supported configuration parameters.
configuration = task_management_sdk.Configuration(
    host = "http://localhost"
)


# Enter a context with an instance of the API client
with task_management_sdk.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = task_management_sdk.TasksApi(api_client)
    status = 'status_example' # str | Filter by status: todo, in_progress, review, done (optional)
    search = 'search_example' # str | Search term in title or description (optional)

    try:
        # List Tasks
        api_response = api_instance.list_tasks_api_tasks_get(status=status, search=search)
        print("The response of TasksApi->list_tasks_api_tasks_get:\n")
        pprint(api_response)
    except Exception as e:
        print("Exception when calling TasksApi->list_tasks_api_tasks_get: %s\n" % e)
```



### Parameters


Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **status** | **str**| Filter by status: todo, in_progress, review, done | [optional] 
 **search** | **str**| Search term in title or description | [optional] 

### Return type

[**List[TaskResponse]**](TaskResponse.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
|-------------|-------------|------------------|
**200** | Successful Response |  -  |
**422** | Validation Error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **read_task_api_tasks_task_id_get**
> TaskResponse read_task_api_tasks_task_id_get(task_id)

Read Task

Get task details by ID.

### Example


```python
import task_management_sdk
from task_management_sdk.models.task_response import TaskResponse
from task_management_sdk.rest import ApiException
from pprint import pprint

# Defining the host is optional and defaults to http://localhost
# See configuration.py for a list of all supported configuration parameters.
configuration = task_management_sdk.Configuration(
    host = "http://localhost"
)


# Enter a context with an instance of the API client
with task_management_sdk.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = task_management_sdk.TasksApi(api_client)
    task_id = 56 # int | 

    try:
        # Read Task
        api_response = api_instance.read_task_api_tasks_task_id_get(task_id)
        print("The response of TasksApi->read_task_api_tasks_task_id_get:\n")
        pprint(api_response)
    except Exception as e:
        print("Exception when calling TasksApi->read_task_api_tasks_task_id_get: %s\n" % e)
```



### Parameters


Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **task_id** | **int**|  | 

### Return type

[**TaskResponse**](TaskResponse.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
|-------------|-------------|------------------|
**200** | Successful Response |  -  |
**422** | Validation Error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **remove_task_api_tasks_task_id_delete**
> remove_task_api_tasks_task_id_delete(task_id)

Remove Task

Delete a task by ID.

### Example


```python
import task_management_sdk
from task_management_sdk.rest import ApiException
from pprint import pprint

# Defining the host is optional and defaults to http://localhost
# See configuration.py for a list of all supported configuration parameters.
configuration = task_management_sdk.Configuration(
    host = "http://localhost"
)


# Enter a context with an instance of the API client
with task_management_sdk.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = task_management_sdk.TasksApi(api_client)
    task_id = 56 # int | 

    try:
        # Remove Task
        api_instance.remove_task_api_tasks_task_id_delete(task_id)
    except Exception as e:
        print("Exception when calling TasksApi->remove_task_api_tasks_task_id_delete: %s\n" % e)
```



### Parameters


Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **task_id** | **int**|  | 

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
|-------------|-------------|------------------|
**204** | Successful Response |  -  |
**422** | Validation Error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **update_task_details_api_tasks_task_id_put**
> TaskResponse update_task_details_api_tasks_task_id_put(task_id, task_update)

Update Task Details

Update task details or status.
NOTE: Status transitions are strictly enforced in order: todo -> in_progress -> review -> done.

### Example


```python
import task_management_sdk
from task_management_sdk.models.task_response import TaskResponse
from task_management_sdk.models.task_update import TaskUpdate
from task_management_sdk.rest import ApiException
from pprint import pprint

# Defining the host is optional and defaults to http://localhost
# See configuration.py for a list of all supported configuration parameters.
configuration = task_management_sdk.Configuration(
    host = "http://localhost"
)


# Enter a context with an instance of the API client
with task_management_sdk.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = task_management_sdk.TasksApi(api_client)
    task_id = 56 # int | 
    task_update = task_management_sdk.TaskUpdate() # TaskUpdate | 

    try:
        # Update Task Details
        api_response = api_instance.update_task_details_api_tasks_task_id_put(task_id, task_update)
        print("The response of TasksApi->update_task_details_api_tasks_task_id_put:\n")
        pprint(api_response)
    except Exception as e:
        print("Exception when calling TasksApi->update_task_details_api_tasks_task_id_put: %s\n" % e)
```



### Parameters


Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **task_id** | **int**|  | 
 **task_update** | [**TaskUpdate**](TaskUpdate.md)|  | 

### Return type

[**TaskResponse**](TaskResponse.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
|-------------|-------------|------------------|
**200** | Successful Response |  -  |
**422** | Validation Error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **update_task_status_only_api_tasks_task_id_status_patch**
> TaskResponse update_task_status_only_api_tasks_task_id_status_patch(task_id, task_status_update)

Update Task Status Only

Advance or change task status.
NOTE: Status transitions are strictly enforced in order: todo -> in_progress -> review -> done.

### Example


```python
import task_management_sdk
from task_management_sdk.models.task_response import TaskResponse
from task_management_sdk.models.task_status_update import TaskStatusUpdate
from task_management_sdk.rest import ApiException
from pprint import pprint

# Defining the host is optional and defaults to http://localhost
# See configuration.py for a list of all supported configuration parameters.
configuration = task_management_sdk.Configuration(
    host = "http://localhost"
)


# Enter a context with an instance of the API client
with task_management_sdk.ApiClient(configuration) as api_client:
    # Create an instance of the API class
    api_instance = task_management_sdk.TasksApi(api_client)
    task_id = 56 # int | 
    task_status_update = task_management_sdk.TaskStatusUpdate() # TaskStatusUpdate | 

    try:
        # Update Task Status Only
        api_response = api_instance.update_task_status_only_api_tasks_task_id_status_patch(task_id, task_status_update)
        print("The response of TasksApi->update_task_status_only_api_tasks_task_id_status_patch:\n")
        pprint(api_response)
    except Exception as e:
        print("Exception when calling TasksApi->update_task_status_only_api_tasks_task_id_status_patch: %s\n" % e)
```



### Parameters


Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **task_id** | **int**|  | 
 **task_status_update** | [**TaskStatusUpdate**](TaskStatusUpdate.md)|  | 

### Return type

[**TaskResponse**](TaskResponse.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
|-------------|-------------|------------------|
**200** | Successful Response |  -  |
**422** | Validation Error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

