@echo off

call C:\playsms\etc\playsms

start "playsmsd" %PLAYSMS_BIN%\playsmsd
start "sendsmsd" %PLAYSMS_BIN%\sendsmsd
