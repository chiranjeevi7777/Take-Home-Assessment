@echo off
echo Exporting latest OpenAPI specification from FastAPI app...
cd backend
python export_openapi.py
cd ..

echo Generating Python SDK using OpenAPI Generator CLI...
call npx -y @openapitools/openapi-generator-cli generate -i openapi.json -g python -o python_sdk --additional-properties=packageName=task_management_sdk

echo SDK generation complete! Generated inside /python_sdk
pause
