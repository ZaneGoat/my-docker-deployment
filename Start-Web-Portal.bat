@echo off
color 0A
echo ===================================================
echo       Docker Web Portal Startup Script v2
echo ===================================================
echo.

:: Step 0 - Ensure Docker daemon is running in Kali
echo [0/5] Checking Docker daemon in Kali Linux...
wsl -d kali-linux -u root -e bash -c "service docker status | grep -q 'running' || service docker start"
echo [OK] Docker daemon verified.
echo.

:: Step 1 - Build and Start Stack via robust script
echo [1/5] Initializing Server and optimizing startup...
wsl -d kali-linux -u root -e bash /home/Zane/Desktop/my-docker-deployment/start_portal.sh

:: Step 2 - Retrieve Ngrok public URL
for /f "delims=" %%i in ('wsl -d kali-linux -u root -e cat /tmp/ngrok_url.txt 2^>nul') do set NGROK_URL=%%i

:: Step 5 - Launch site selector
echo.
echo ===================================================
color 0B
echo   PORTAL LIVE AND READY - CHOOSE YOUR SITE:
echo ===================================================
echo.
echo   [M] Master Cyberpunk Portal (All Apps)   http://127.0.0.1/
echo   [1] IPIRNET Admin Portal                 http://127.0.0.1/ipirnet/
echo   [2] Flask App - Ihsan                    http://127.0.0.1/ihsan/
echo   [3] Flask App - Khadija (Patisserie)     http://127.0.0.1/khadija/
echo   [4] Flask App - Traiteur Evenements      http://127.0.0.1/traiteur/
echo   [5] Flask App - Reservation Terrain      http://127.0.0.1/terrain/
echo   [6] Flask App - PizzaTime                http://127.0.0.1/pizzatime/
echo   [7] Flask App - Hotel Management         http://127.0.0.1/hotel/
echo   [8] Flask App - Othman Terrain           http://127.0.0.1/othman-terrain/
echo   [9] Open Ngrok Public URL (Internet)
echo   [10] View Running Containers
echo   [11] Stop All Containers
echo   [0] Exit
echo.

if not "%NGROK_URL%"=="" (
    color 0A
    echo   NGROK URL: %NGROK_URL%
) else (
    color 0E
    echo   [WARN] Ngrok URL not available yet. Try option 9 later.
)
color 0B
echo.
set /p CHOICE=  Enter choice: 

if /i "%CHOICE%"=="M" start "" "http://127.0.0.1/"
if "%CHOICE%"=="1" start "" "http://127.0.0.1/ipirnet/"
if "%CHOICE%"=="2" start "" "http://127.0.0.1/ihsan/"
if "%CHOICE%"=="3" start "" "http://127.0.0.1/khadija/"
if "%CHOICE%"=="4" start "" "http://127.0.0.1/traiteur/"
if "%CHOICE%"=="5" start "" "http://127.0.0.1/terrain/"
if "%CHOICE%"=="6" start "" "http://127.0.0.1/pizzatime/"
if "%CHOICE%"=="7" start "" "http://127.0.0.1/hotel/"
if "%CHOICE%"=="8" start "" "http://127.0.0.1/othman-terrain/"
if "%CHOICE%"=="9" (
    if not "%NGROK_URL%"=="" (
        start "" "%NGROK_URL%"
    ) else (
        echo [ERR] Ngrok URL not available.
    )
)
if "%CHOICE%"=="10" (
    echo.
    wsl -d kali-linux -u root -e bash -c "docker ps --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}'"
)
if "%CHOICE%"=="11" (
    echo.
    echo Stopping all containers...
    wsl -d kali-linux -u root -e bash -c "cd /home/Zane/Desktop/my-docker-deployment && docker compose down"
    echo [OK] All containers stopped.
)

echo.
pause
