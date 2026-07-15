<?php

/**
 * Embedding service configuration.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Embedding Service URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the Python FastAPI embedding service.
    |
    */
    'service_url' => env('EMBEDDING_SERVICE_URL', 'http://localhost:8001'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Request timeout in seconds for embedding service calls.
    |
    */
    'timeout' => (int) env('EMBEDDING_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry
    |--------------------------------------------------------------------------
    |
    | Number of retry attempts on failure.
    |
    */
    'retries' => (int) env('EMBEDDING_RETRIES', 2),

];
