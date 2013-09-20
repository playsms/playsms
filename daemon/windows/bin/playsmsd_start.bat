@echo off

call C:\playsms\etc\playsms.bat

start "playsmsd" %PLAYSMS_BIN%\playsmsd
start "recvsmsd" %PLAYSMS_BIN%\sendsmsd
start "sendsmsd" %PLAYSMS_BIN%\sendsmsd
start "dlrd" %PLAYSMS_BIN%\sendsmsd

