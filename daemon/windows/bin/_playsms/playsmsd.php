#!/usr/bin/php -q
<?php

set_time_limit(600);

//error_reporting(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

// The path to directory of installed playsms
$PLAYSMS_PATH = $argv[1];

// DO NOT CHANGE ANYTHING BELOW THE LINE
// ------------------------------------------------------
if (file_exists($PLAYSMS_PATH)) {
	chdir($PLAYSMS_PATH);

	// mark this process as a DAEMON_PROCESS
	$DAEMON_PROCESS = true;

	if (file_exists('init.php')) {
		include 'init.php';
		$fn = $apps_path['libs'].'/function.php';
		if ($core_config['daemon_process'] && file_exists($fn)) {
			include $fn;
			if ($apps_path['incs']) {
				echo "begin cycling\n";
				playsmsd();
				echo "session:".mktime()."\n";
				echo "end cycling\n";
			}
		}
	}
}

?>