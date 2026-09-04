@echo off
echo Launching Task Management System Backend and Frontend...
start "FastAPI Backend" cmd /c "run_backend.bat"
timeout /t 3
start "React Frontend" cmd /c "run_frontend.bat"
echo System services started!
