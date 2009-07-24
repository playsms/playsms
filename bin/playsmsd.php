#!/usr/bin/php -q
<?php

// The path to directory of installed playsms
$PLAYSMS_PATH = $argv[1];

// DO NOT CHANGE ANYTHING BELOW THE LINE
// ------------------------------------------------------
if (file_exists($PLAYSMS_PATH))
{
    chdir($PLAYSMS_PATH);

    $DAEMON_PROCESS = true;

    if (file_exists("init.php"))
    {
	include "init.php";
    }
    
    $fn = $apps_path['libs']."/function.php";
    if (file_exists($fn))
    {
	include $fn;
    }

    if ($apps_path['incs'])
    {
	echo "begin cycling\n";
	playsmsd();
	getsmsinbox();
	getsmsstatus();
	execgwcustomcmd();
	execcommoncustomcmd();
	echo "session:".mktime()."\n";
	echo "end cycling\n";
    }
}

?>