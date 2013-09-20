@echo off

TASKKILL /F /FI "WINDOWTITLE eq playsmsd*"
TASKKILL /F /FI "WINDOWTITLE eq recvsmsd*"
TASKKILL /F /FI "WINDOWTITLE eq sendsmsd*"
TASKKILL /F /FI "WINDOWTITLE eq dlrssmsd*"


