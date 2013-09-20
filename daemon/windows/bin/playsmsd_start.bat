@echo off

call C:\playsms\etc\playsms.bat

start "playsmsd" %PLAYSMS_BIN%\playsmsd
start "recvsmsd" %PLAYSMS_BIN%\sendsmsd
start "sendsmsd" %PLAYSMS_BIN%\sendsmsd
start "dlrssmsd" %PLAYSMS_BIN%\sendsmsd

