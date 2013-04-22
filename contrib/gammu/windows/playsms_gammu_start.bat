@echo off

call C:\playsms\etc\playsms.bat

start "gammu" %GAMMU_BIN%\gammu-smsd -c C:\playsms\etc\gammu-smsdrc
start "playsmsd_start" %PLAYSMS_BIN%\playsmsd_start.bat
TASKKILL /F /FI "WINDOWTITLE eq playsmsd_start*"
