@echo off
title ZaneAI - The Child
color 0A
echo.
echo ===================================================
echo   INITIALIZING ZANE_AI: THE CHILD CONSTRUCT
echo ===================================================
echo.
echo Waking up the Kali Linux neural net...
wsl -d kali-linux -u root -e bash -c "/opt/pygpt/bin/streamlit run /mnt/c/Users/user/Desktop/ZaneX/my-docker-deployment/ZaneAI/custom_gemini_app.py"
pause
