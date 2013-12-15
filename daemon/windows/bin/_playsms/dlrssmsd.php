#!/usr/bin/php -q
<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

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
				dlrd();
				getsmsstatus();
				echo "session:".mktime()."\n";
				echo "end cycling\n";
			}
		}
	}
}

