@echo off
title Docker Portal One-Click Launcher
color 0A
echo ===================================================
echo       Launching Docker Portal Stack...
echo ===================================================
echo.

:: 1. Ensure Docker daemon is running in Kali Linux
echo [1/3] Verifying Docker daemon...
wsl -d kali-linux -u root -e bash -c "service docker status | grep -q 'running' || service docker start"

:: 2. Start Docker Compose stack
echo [2/3] Starting containers...
wsl -d kali-linux -u root -e bash -c "cd /home/Zane/Desktop/my-docker-deployment && docker compose up -d"

:: 3. Open Master Cyberpunk Web Portal in browser
echo [3/3] Opening Web Portal...
timeout /t 3 /nobreak > nul
start "" "http://127.0.0.1/"

echo.
echo [OK] All apps live at http://127.0.0.1/
pause
