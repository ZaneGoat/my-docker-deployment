@echo off
color 0C
echo ===================================================
echo       Stopping Docker Deployment
echo ===================================================
echo.
wsl -d kali-linux -u root -e bash -c "cd /home/Zane/Desktop/my-docker-deployment && docker compose down"
echo.
echo All containers stopped.
pause
