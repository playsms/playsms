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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */

// Usage:
// playsmsd [<PLAYSMSD_CONF>] <COMMAND> <LOOP_FLAG> <CMD_PARAM>
set_time_limit(0);

//error_reporting(0);

// check CLI
if (!(PHP_SAPI == 'cli')) {
	echo "playsmsd must be called from cli" . PHP_EOL;

	exit();
}

// prevent running this script as root
if (function_exists('posix_getuid') && posix_getuid() === 0) {
	echo "playsmsd must not run as root" . PHP_EOL;

	exit();
}

// declare this script started from playSMS daemon
$DAEMON_PROCESS = true;
define('_DAEMON_PROCESS_', true);
$core_config['daemon_process'] = true;

// Let's start

$core_config['daemon']['PLAYSMSD_CONF'] = '';
$argument = $argv + array_fill(0, 5, '');

// daemons
$core_config['daemon']['PLAYSMSD_DAEMONS'] = [
	'schedule',
	'ratesmsd',
	'dlrssmsd',
	'recvsmsd',
	'sendsmsd',
];

// child daemons
$core_config['daemon']['PLAYSMSD_CHILD_DAEMONS'] = [
	'sendqueue',
	'recvqueue',
	'schdqueue',
];

// check if 1st argv is playsmsd.conf path
if ($argument[1] && is_file($argument[1])) {
	$core_config['daemon']['PLAYSMSD_CONF'] = $argument[1];
	array_shift($argument);
}

$ini = [];
$ini_files = [];

// fixme anton - get HOME dir in Linux
// ref: https://stackoverflow.com/questions/1894917/how-to-get-the-home-directory-from-a-php-cli-script
$home_dir = isset($_SERVER['HOME']) && $_SERVER['HOME'] ? $_SERVER['HOME'] : getenv("HOME");
$core_config['daemon']['HOME_DIR'] = is_dir($home_dir) ? $home_dir : '';

if ($core_config['daemon']['PLAYSMSD_CONF']) {
	$ini_files = [
		$core_config['daemon']['PLAYSMSD_CONF']
	];
} else {
	$ini_files = [
		$core_config['daemon']['HOME_DIR'] . '/playsmsd.conf',
		$core_config['daemon']['HOME_DIR'] . '/etc/playsmsd.conf',
		'./playsmsd.conf',
		'/playsmsd.conf',
		'./etc/playsmsd.conf',
		'/etc/playsmsd.conf',
		'/usr/local/etc/playsmsd.conf',
	];
	$ini_files = array_unique($ini_files);
}

$continue = false;
foreach ( $ini_files as $core_config['daemon']['PLAYSMSD_CONF'] ) {
	$core_config['daemon']['PLAYSMSD_CONF_REALPATH'] = realpath($core_config['daemon']['PLAYSMSD_CONF']);
	$core_config['daemon']['PLAYSMSD_CONF'] = $core_config['daemon']['PLAYSMSD_CONF_REALPATH'] !== false ? $core_config['daemon']['PLAYSMSD_CONF_REALPATH'] : $core_config['daemon']['PLAYSMSD_CONF'];
	if ($core_config['daemon']['PLAYSMSD_CONF'] && is_file($core_config['daemon']['PLAYSMSD_CONF'])) {
		$ini = parse_ini_file($core_config['daemon']['PLAYSMSD_CONF']);
		if ($ini['PLAYSMS_PATH'] && $ini['PLAYSMS_BIN'] && $ini['PLAYSMS_LOG']) {
			$ini['PLAYSMSD_CONF'] = $core_config['daemon']['PLAYSMSD_CONF'];
			$continue = true;
			break;
		}
	}
}

if (!$continue) {
	echo "Unable to find playsmsd.conf" . PHP_EOL;
	exit();
}

$core_config['daemon'] += $ini;

// playSMS installation location
$core_config['daemon']['PLAYSMS_INSTALL_PATH'] = $ini['PLAYSMS_PATH'] ?: '/var/www/playsms';

// playSMS lib location
$core_config['daemon']['PLAYSMS_LIB_PATH'] = $ini['PLAYSMS_LIB'] ?: '/var/lib/playsms';

// playSMS daemon location
$core_config['daemon']['PLAYSMS_DAEMON_PATH'] = $ini['PLAYSMS_BIN'] ?: '/usr/local/bin';

// playSMS log location
$core_config['daemon']['PLAYSMS_LOG_PATH'] = $ini['PLAYSMS_LOG'] ?: '/var/log/playsms';

// set default DAEMON_SLEEP at 1 second
$core_config['daemon']['DAEMON_SLEEP'] = $ini['DAEMON_SLEEP'] >= 1 ? $ini['DAEMON_SLEEP'] : 1;

// set PHP error reporting level
$core_config['daemon']['ERROR_REPORTING'] = isset($ini['ERROR_REPORTING']) ? (int) $ini['ERROR_REPORTING'] : E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED;

error_reporting($core_config['daemon']['ERROR_REPORTING']);

// Daemon service
$COMMAND = strtolower($argument[1]);

// Loop flag: loop => execute in a loop, once => execute only once
$LOOP_FLAG = strtolower($argument[2]) ?: 'loop';

// Service parameters
$COMMAND_PARAM = $argument[3];

// playsmsd
$core_config['daemon']['PLAYSMSD_BIN'] = $core_config['daemon']['PLAYSMS_DAEMON_PATH'] . "/playsmsd";
$core_config['daemon']['PLAYSMSD_COMMAND'] = $core_config['daemon']['PLAYSMSD_BIN'] . " " . $core_config['daemon']['PLAYSMSD_CONF'];

if (is_dir($core_config['daemon']['PLAYSMS_INSTALL_PATH'])) {
	chdir($core_config['daemon']['PLAYSMS_INSTALL_PATH']);

	$continue = false;
	if (is_file('init.php')) {
		require 'init.php';
		$fn = $core_config['apps_path']['libs'] . '/function.php';
		if ($core_config['daemon_process'] && is_file($fn)) {
			require $fn;
			if ($core_config['apps_path']['incs']) {
				$continue = true;
			}
		}
	}

	if (!$continue) {
		echo "playSMS installation not found" . PHP_EOL;

		exit();
	}

	// save playsmsd data in database for other plugins such as playsmslog
	if ($json = playsmsd_check(true, false)) {
		$json_array = json_decode($json, true);
		if (!empty($json_array['IS_RUNNING'])) {
			$items = [
				'time_start' => time(),
				'last_update' => time(),
				'data' => $json
			];
		} else {
			$items = [
				'last_update' => time(),
				'data' => $json
			];

		}
		registry_update(0, 'core', 'playsmsd', $items);
	}

	switch ($COMMAND) {
		case 'watchdog':

			// sanitize check interval
			$interval = (int) $COMMAND_PARAM;
			$interval_minimum = 60;
			if ($interval < $interval_minimum) {
				$interval = $interval_minimum;
			}

			do {
				// start playsmsd services
				playsmsd_start();

				// watch playsmsd services
				do {
					sleep($interval);
				} while (playsmsd_isrunning());

				// show which services stopped, before any further actions
				playsmsd_pids_show();

				// stop playsmsd services
				playsmsd_stop();

				// stop playsmsd child scripts
				playsmsd_child_stop();

				// restart if specified
			} while ($LOOP_FLAG === 'loop' && sleep(2) == 0);

			// treat any stopped subprocess as an error 
			exit(1);

		case 'start':

			// start playsmsd services
			playsmsd_start();

			exit();

		case 'stop':

			// stop playsmsd services
			playsmsd_stop();

			// stop playsmsd child scripts
			playsmsd_child_stop();

			exit();

		case 'restart':

			// stop, wait for 2 seconds and then start
			playsmsd_stop();
			sleep(2);
			playsmsd_start();

			exit();

		case 'status':

			if (playsmsd_isrunning()) {
				echo "playsmsd is running" . PHP_EOL;
				playsmsd_pids_show();
			} else {
				echo "playsmsd is not running" . PHP_EOL;
			}

			exit();

		case 'check':

			// non-JSON output
			playsmsd_check(false);

			exit();

		case 'check_json':

			// JSON output
			playsmsd_check(true);

			exit();

		case 'log':

			// View log
			$debug_file = $argument[2] ?: '';
			playsmsd_log($debug_file);

			exit();

		case 'version':

			echo core_get_version() . PHP_EOL;

			exit();
	}

	if (!$COMMAND) {
		echo "Usage: playsmsd <start|stop|restart|status|check|check_json|log|version>" . PHP_EOL;

		exit();
	}

	if ($LOOP_FLAG == 'once') {

		// execute one time only

		// MAIN ONCE BLOCK

		//echo $COMMAND . " start time:" . time() . PHP_EOL;

		if ($COMMAND == 'sendqueue') {
			if ($COMMAND_PARAM) {
				$param = explode('_', $COMMAND_PARAM);
				if (isset($param[0]) && isset($param[1]) && $param[0] == 'Q' && $queue = core_sanitize_alphanumeric($param[1])) {
					$chunk = isset($param[2]) && (int) $param[2] ? (int) $param[2] : 0;
					sendsmsd($queue, $chunk);
				}
			}
		}

		if ($COMMAND == 'recvqueue') {
			if ($COMMAND_PARAM) {
				$param = explode('_', $COMMAND_PARAM);
				if (isset($param[0]) && isset($param[1]) && $param[0] == 'ID' && $id = (int) $param[1]) {
					recvsms_queue($id);
				}
			}
		}

		if ($COMMAND == 'schdqueue') {
			if ($COMMAND_PARAM) {
				playsmsd_once($COMMAND_PARAM);
			}
		}

		// END OF ONCE BLOCK

		//echo $COMMAND . " end time:" . time() . PHP_EOL;

		exit();
	} else if ($LOOP_FLAG == 'loop') {

		// execute in a loop

		$DAEMON_LOOPING = true;

		while ($DAEMON_LOOPING) {

			//echo $COMMAND . " start time:" . time() . PHP_EOL;

			// update last_update data
			registry_update(0, 'core', 'playsmsd', ['last_update' => time()]);


			// re-include init.php on every 'while' to get the most updated configurations
			include 'init.php';

			// MAIN LOOP BLOCK

			switch ($COMMAND) {
				case 'schedule':
					playsmsd();
					break;

				case 'ratesmsd':
					rate_update();
					break;

				case 'dlrssmsd':
					dlrd();
					getsmsstatus();
					break;

				case 'recvsmsd':
					recvsmsd();
					getsmsinbox();
					break;

				case 'sendsmsd':

					// init phase
					// $core_config['sendsmsd_queue'] = number of simultaneous queues
					// $core_config['sendsmsd_chunk'] = number of chunk per queue
					// $core_config['sendsmsd_chunk_size'] = max number of sms per chunk

					// select id and queue_code from table queue
					// where the queue hasn't been processed yet and it's already scheduled to run
					// but limit the search up to the number of sendsmsd_queue
					$db_query = "
						SELECT id, queue_code FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue 
						WHERE flag=0 AND datetime_scheduled<=? 
						LIMIT " . (int) $core_config['sendsmsd_queue'];
					$db_result = dba_query($db_query, [core_get_datetime()]);
					while ($db_row = dba_fetch_array($db_result)) {
						// $db_row['queue_code'] = queue code
						// $db_row['queue_count'] = number of entries in a queue
						// $db_row['sms_count'] = number of SMS in an entry
						$num = 0;

						// look for destinations from table queue dst based on queue_id
						// and give them chunk number
						$db_query2 = "SELECT id FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst WHERE queue_id=?";
						$db_result2 = dba_query($db_query2, [$db_row['id']]);
						while ($db_row2 = dba_fetch_array($db_result2)) {
							$num++;
							if ((int) $core_config['sendsmsd_chunk_size'] > 0 && $chunk = floor($num / (int) $core_config['sendsmsd_chunk_size'])) {
								$db_query3 = "UPDATE " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst SET chunk=? WHERE id=?";
								dba_affected_rows($db_query3, [$chunk, $db_row2['id']]);
							}
						}

						if ($num > 0) {
							// destination found, update queue to process step
							if (!sendsms_queue_update($db_row['queue_code'], ['flag' => 3])) {
								_log('fail to update queue for processing queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
							}
						} else {
							// no destination found, something's not right with the queue, mark it as done and failed (flag 2)
							if (sendsms_queue_update($db_row['queue_code'], ['flag' => 2])) {
								_log('enforce init finish queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
							} else {
								_log('fail to enforce init finish queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
							}
						}
					}

					// process phase
					$queue = [];

					// look for queues that ready for processing
					$db_query = "SELECT id, queue_code FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE flag=3";
					$db_result = dba_query($db_query);
					while ($db_row = dba_fetch_array($db_result)) {
						// get chunks and prepare queue list in $queue
						$db_query2 = "SELECT chunk FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst WHERE queue_id=? AND flag=0 GROUP BY chunk LIMIT " . (int) $core_config['sendsmsd_chunk'];
						$db_result2 = dba_query($db_query2, [$db_row['id']]);
						while ($db_row2 = dba_fetch_array($db_result2)) {
							if ($db_row['queue_code']) {
								$queue[] = 'Q_' . core_sanitize_alphanumeric($db_row['queue_code']) . '_' . (int) $db_row2['chunk'];
							}
						}

						if (count($queue) < 1) {
							// no chunk found, something's not right with the queue, mark it as done and failed (flag 2)
							if (sendsms_queue_update($db_row['queue_code'], ['flag' => 2])) {
								_log('enforce finish process queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
							} else {
								_log('fail to enforce finish process queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
							}
						}
					}

					// execute phase
					$queue = array_unique($queue);
					if (count($queue) > 0) {
						foreach ( $queue as $q ) {
							// if found queue and it's not currently running, then run it
							if ($q && !playsmsd_pid_get($q)) {
								$RUN_THIS = "nohup " . $core_config['daemon']['PLAYSMSD_COMMAND'] . " sendqueue once " . $q . " >/dev/null 2>&1 &";
								//echo $COMMAND . " execute:" . $RUN_THIS . PHP_EOL;
								shell_exec($RUN_THIS);
							}
						}
					}
					break;

				default:
					$DAEMON_LOOPING = false;
			}

			// END OF MAIN LOOP BLOCK


			//echo $COMMAND . " end time:" . time() . PHP_EOL;


			sleep($core_config['daemon']['DAEMON_SLEEP']);

			// empty buffer, yes doubled :)
			ob_end_flush();
			ob_end_flush();
		}

		// while true
	}
}

