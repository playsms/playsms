@echo off

call C:\playsms\etc\playsms.bat

:while1
	%PHP_CLI% %PLAYSMS_BIN%\_playsms\dlrssmsd.php %PLAYSMS_PATH%
	timeout %REFRESH_RATE%
goto :while1
