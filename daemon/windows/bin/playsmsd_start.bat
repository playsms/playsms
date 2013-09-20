@echo off

call C:\playsms\etc\playsms.bat

start "playsmsd" %PLAYSMS_BIN%\_playsms\playsmsd
start "recvsmsd" %PLAYSMS_BIN%\_playsms\sendsmsd
start "sendsmsd" %PLAYSMS_BIN%\_playsms\sendsmsd
start "dlrssmsd" %PLAYSMS_BIN%\_playsms\sendsmsd

