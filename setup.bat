@echo off
echo ===================================================
echo   Task Management System - Automated Project Setup
echo ===================================================

echo [1/4] Installing Python Backend Dependencies...
cd backend
python -m pip install -r requirements.txt
if %ERRORLEVEL% NEQ 0 (
    echo Error installing backend requirements!
    pause
    exit /b %ERRORLEVEL%
)

echo [2/4] Exporting OpenAPI Specification...
python export_openapi.py
cd ..

echo [3/4] Installing React Frontend Dependencies...
cd frontend
call npm install
cd ..

echo [4/4] Generating Python SDK via OpenAPI Generator CLI...
call npx -y @openapitools/openapi-generator-cli generate -i openapi.json -g python -o python_sdk --additional-properties=packageName=task_management_sdk

echo ===================================================
echo   Setup Completed Successfully!
echo   Run 'run_backend.bat' and 'run_frontend.bat' to start services.
echo ===================================================
pause
