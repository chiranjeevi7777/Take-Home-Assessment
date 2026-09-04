# task_management_sdk.WorkflowApi

All URIs are relative to *http://localhost*

Method | HTTP request | Description
------------- | ------------- | -------------
[**get_workflow_rules_api_workflow_rules_get**](WorkflowApi.md#get_workflow_rules_api_workflow_rules_get) | **GET** /api/workflow/rules | Get Workflow Rules


# **get_workflow_rules_api_workflow_rules_get**
> object get_workflow_rules_api_workflow_rules_get()

Get Workflow Rules

Returns the task status order and transition rules.

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
    api_instance = task_management_sdk.WorkflowApi(api_client)

    try:
        # Get Workflow Rules
        api_response = api_instance.get_workflow_rules_api_workflow_rules_get()
        print("The response of WorkflowApi->get_workflow_rules_api_workflow_rules_get:\n")
        pprint(api_response)
    except Exception as e:
        print("Exception when calling WorkflowApi->get_workflow_rules_api_workflow_rules_get: %s\n" % e)
```



### Parameters

This endpoint does not need any parameter.

### Return type

**object**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
|-------------|-------------|------------------|
**200** | Successful Response |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

