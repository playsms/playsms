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

// functions


/**
 * Get pid for certain playsmsd process
 *
 * @param string $process
 *        process name
 * @return int PID
 */
function playsmsd_pid_get($process)
{
	global $PLAYSMSD_CONF;

	$ret = 0;

	if ($PLAYSMSD_CONF && $process) {
		$ret = (int) trim(shell_exec("ps -eo pid,command | grep '" . $PLAYSMSD_CONF . "' | grep '" . $process . "' | grep -v grep | sed -e 's/^ *//' -e 's/ *$//' | cut -d' ' -f1 | tr '\n' ' '"));
	} else {
		$ret = 0;
	}

	return $ret;
}

/**
 * Get pids for all playsmsd main process
 *
 * @return array PIDs
 */
function playsmsd_pids()
{
	$pids['schedule'] = playsmsd_pid_get('schedule');
	$pids['ratesmsd'] = playsmsd_pid_get('ratesmsd');
	$pids['dlrssmsd'] = playsmsd_pid_get('dlrssmsd');
	$pids['recvsmsd'] = playsmsd_pid_get('recvsmsd');
	$pids['sendsmsd'] = playsmsd_pid_get('sendsmsd');

	return $pids;
}

/**
 * Show pids
 */
function playsmsd_pids_show()
{
	$pids = playsmsd_pids();
	echo "schedule at pid " . $pids['schedule'] . "" . PHP_EOL;
	echo "ratesmsd at pid " . $pids['ratesmsd'] . "" . PHP_EOL;
	echo "dlrssmsd at pid " . $pids['dlrssmsd'] . "" . PHP_EOL;
	echo "recvsmsd at pid " . $pids['recvsmsd'] . "" . PHP_EOL;
	echo "sendsmsd at pid " . $pids['sendsmsd'] . "" . PHP_EOL;
}

/**
 * Check whether or not playsmsd processes are running
 *
 * @return bool true if all processes are running
 */
function playsmsd_isrunning()
{
	$isrunning = false;

	$pids = playsmsd_pids();
	foreach ( $pids as $pid ) {
		if ($pid) {
			$isrunning = true;
		} else {
			$isrunning = false;
			break;
		}
	}

	return $isrunning;
}

/**
 * Start playsmsd scripts
 */
function playsmsd_start()
{
	global $PLAYSMSD_COMMAND;

	if (playsmsd_isrunning()) {
		echo "playsmsd is already running" . PHP_EOL;
		playsmsd_pids_show();
		exit();
	}

	// stop all daemons
	shell_exec("$PLAYSMSD_COMMAND stop >/dev/null 2>&1");
	sleep(2);

	// run playsmsd services
	shell_exec("nohup $PLAYSMSD_COMMAND schedule >/dev/null 2>&1 &");
	shell_exec("nohup $PLAYSMSD_COMMAND ratesmsd >/dev/null 2>&1 &");
	shell_exec("nohup $PLAYSMSD_COMMAND dlrssmsd >/dev/null 2>&1 &");
	shell_exec("nohup $PLAYSMSD_COMMAND recvsmsd >/dev/null 2>&1 &");
	shell_exec("nohup $PLAYSMSD_COMMAND sendsmsd >/dev/null 2>&1 &");

	if (playsmsd_isrunning()) {
		echo "playsmsd has been started" . PHP_EOL;
		playsmsd_pids_show();
	} else {
		echo "Unable to start playsmsd" . PHP_EOL;
	}
}

/**
 * Stop playsmsd scripts
 */
function playsmsd_stop()
{
	$pids = playsmsd_pids();
	foreach ( $pids as $key => $val ) {
		if ($key && $val) {
			echo $key . " at pid " . $val . " will be killed.." . PHP_EOL;
			shell_exec("kill " . $val . " >/dev/null 2>&1");
		}
	}

	if (playsmsd_isrunning()) {
		echo "Unable to stop playsmsd" . PHP_EOL;
		playsmsd_pids_show();
	} else {
		echo "playsmsd has been stopped" . PHP_EOL;
	}
}

/**
 * Stop child scripts
 */
function playsmsd_stop_childs()
{
	$pids['sendqueue'] = playsmsd_pid_get('sendqueue');
	foreach ( $pids as $key => $val ) {
		if ($key && $val) {
			echo $key . " at pid " . $val . " will be killed.." . PHP_EOL;
			shell_exec("kill " . $val . " >/dev/null 2>&1");
		}
	}
}

/**
 * Check variables and states of playsmsd
 *
 * @param bool $json true for json output data
 * @param bool $echo true for print data
 * @return string data
 */
function playsmsd_check($json = false, $echo = true)
{
	global $PLAYSMSD_CONF, $DAEMON_SLEEP, $ERROR_REPORTING;
	global $PLAYSMS_INSTALL_PATH, $PLAYSMS_LIB_PATH, $PLAYSMS_DAEMON_PATH, $PLAYSMS_LOG_PATH;

	$ret = '';

	$data = array(
		'PLAYSMSD_CONF' => $PLAYSMSD_CONF,
		'PLAYSMS_PATH' => $PLAYSMS_INSTALL_PATH,
		'PLAYSMS_LIB' => $PLAYSMS_LIB_PATH,
		'PLAYSMS_BIN' => $PLAYSMS_DAEMON_PATH,
		'PLAYSMS_LOG' => $PLAYSMS_LOG_PATH,
		'DAEMON_SLEEP' => $DAEMON_SLEEP,
		'ERROR_REPORTING' => $ERROR_REPORTING,
		'IS_RUNNING' => playsmsd_isrunning(),
		'PIDS' => playsmsd_pids()
	);

	if ($json) {
		$ret = json_encode($data);
	} else {
		foreach ( $data as $key => $val ) {
			if (is_array($val)) {
				foreach ( $val as $k => $v ) {
					$ret .= $key . " " . $k . " = " . $v . "" . PHP_EOL;
				}
			} else {
				$ret .= $key . " = " . $val . "" . PHP_EOL;
			}
		}
	}

	if ($echo) {
		echo $ret;
	}

	return $ret;
}

/**
 * View log
 *
 * @param string $debug_file Save log to debug file
 */
function playsmsd_log($debug_file = '')
{
	global $PLAYSMS_LOG_PATH;

	$log = $PLAYSMS_LOG_PATH . '/playsms.log';
	if (file_exists($log)) {

		$process = 'tail -n 0 -f ' . $log . ' 2>&1';
		if ($debug_file) {
			@shell_exec('touch ' . $debug_file);
			if (file_exists($debug_file)) {
				$process .= '| tee ' . $debug_file;
			}
		}

		$handle = popen($process, 'r');
		while (!feof($handle)) {
			$buffer = fgets($handle);
			echo $buffer;
			flush();
		}
		pclose($handle);
	}
}

// main procedure


$PLAYSMSD_CONF = '';
$argument = $argv;
$argument[1] = isset($argument[1]) && $argument[1] ? $argument[1] : '';
$argument[2] = isset($argument[2]) && $argument[2] ? $argument[2] : '';

// check if 1st argv is playsmsd.conf path
if (file_exists($argument[1])) {
	$PLAYSMSD_CONF = $argument[1];
	array_shift($argument);
}

$ini = array();
$ini_files = array();

if ($PLAYSMSD_CONF) {
	$ini_files = array($PLAYSMSD_CONF);
} else {
	$ini_files = array(
		'./playsmsd.conf',
		'~/playsmsd.conf',
		'~/etc/playsmsd.conf',
		'/etc/playsmsd.conf',
		'/usr/local/etc/playsmsd.conf',
		'/usr/bin/playsmsd.conf',
		'/usr/local/bin/playsmsd.conf'
	);
}

$continue = false;
foreach ( $ini_files as $PLAYSMSD_CONF ) {
	if ($PLAYSMSD_CONF && file_exists($PLAYSMSD_CONF)) {
		$ini = parse_ini_file($PLAYSMSD_CONF);
		if ($ini['PLAYSMS_PATH'] && $ini['PLAYSMS_BIN'] && $ini['PLAYSMS_LOG']) {
			$continue = true;
			break;
		}
	}
}

if (!$continue) {
	echo "Unable to find playsmsd.conf" . PHP_EOL;
	exit();
}

// playSMS installation location
$PLAYSMS_INSTALL_PATH = ($ini['PLAYSMS_PATH'] ? $ini['PLAYSMS_PATH'] : '/var/www/playsms');

// playSMS lib location
$PLAYSMS_LIB_PATH = ($ini['PLAYSMS_LIB'] ? $ini['PLAYSMS_LIB'] : '/var/lib/playsms');

// playSMS daemon location
$PLAYSMS_DAEMON_PATH = ($ini['PLAYSMS_BIN'] ? $ini['PLAYSMS_BIN'] : '/usr/local/bin');

// playSMS log location
$PLAYSMS_LOG_PATH = ($ini['PLAYSMS_LOG'] ? $ini['PLAYSMS_LOG'] : '/var/log/playsms');

// set default DAEMON_SLEEP at 1 second
$DAEMON_SLEEP = ($ini['DAEMON_SLEEP'] >= 1 ? $ini['DAEMON_SLEEP'] : 1);

// set PHP error reporting level
$ERROR_REPORTING = isset($ini['ERROR_REPORTING']) ? (int) $ini['ERROR_REPORTING'] : E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED;

error_reporting($ERROR_REPORTING);

$core_config['daemon'] = $ini;

// Daemon service
$COMMAND = strtolower($argument[1]);

// Loop flag: loop => execute in a loop, once => execute only once
$LOOP_FLAG = (strtolower($argument[2]) ? strtolower($argument[2]) : 'loop');

// Service parameters
$CMD_PARAM = $argument[3];

// playsmsd
$PLAYSMSD_BIN = "$PLAYSMS_DAEMON_PATH/playsmsd";
$PLAYSMSD_COMMAND = $PLAYSMSD_BIN . ' ' . $PLAYSMSD_CONF;

switch ($COMMAND) {
	case 'watchdog':
	case 'start':

		// start playsmsd services
		playsmsd_start();

		exit();

	case 'stop':

		// stop playsmsd services
		playsmsd_stop();

		// stop playsmsd child scripts
		playsmsd_stop_childs();

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
		$debug_file = ($argument[2] ? $argument[2] : '');
		playsmsd_log($debug_file);

		exit();
}

if (!$COMMAND) {
	echo "Usage: playsmsd <start|stop|restart|status|check|check_json|log|version>" . PHP_EOL;
	exit();
}

if (file_exists($PLAYSMS_INSTALL_PATH)) {
	chdir($PLAYSMS_INSTALL_PATH);

	// mark this process as a DAEMON_PROCESS
	$DAEMON_PROCESS = true;

	$continue = false;
	if (file_exists('init.php')) {
		require 'init.php';
		$fn = $core_config['apps_path']['libs'] . '/function.php';
		if ($core_config['daemon_process'] && file_exists($fn)) {
			require $fn;
			if ($core_config['apps_path']['incs']) {
				$continue = true;
			}
		}
	}

	// save playsmsd data in database for other plugins such as playsmslog
	if ($continue && $json = playsmsd_check(true, false)) {
		$json_array = json_decode($json, true);
		if (isset($json_array['IS_RUNNING']) && (bool) $json_array['IS_RUNNING'] ? true : false) {
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

	if ($continue && $COMMAND == 'version') {

		echo core_get_version() . PHP_EOL;

		exit();
	} else if ($continue && $LOOP_FLAG == 'once') {

		// execute one time only


		// MAIN ONCE BLOCK


		//echo $COMMAND . " start time:" . time() . "" . PHP_EOL;


		if ($COMMAND == 'sendqueue') {
			if ($CMD_PARAM) {
				$param = explode('_', $CMD_PARAM);
				if (($param[0] == 'Q') && ($queue = $param[1])) {
					$chunk = ((int) $param[2] ? (int) $param[2] : 0);
					sendsmsd($queue, $chunk);
				}
			}
		}

		if ($COMMAND == 'playsmsd') {
			if ($CMD_PARAM) {
				playsmsd_once($CMD_PARAM);
			}
		}

		// END OF ONCE BLOCK


		//echo $COMMAND . " end time:" . time() . "" . PHP_EOL;


		exit();
	} else if ($continue && $LOOP_FLAG == 'loop') {

		// execute in a loop


		$DAEMON_LOOPING = true;

		while ($DAEMON_LOOPING) {

			//echo $COMMAND . " start time:" . time() . "" . PHP_EOL;

			// update last_update data
			registry_update(0, 'core', 'playsmsd', [
				'last_update' => time()
			]);


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

					// init step
					// $core_config['sendsmsd_queue'] = number of simultaneous queues
					// $core_config['sendsmsd_chunk'] = number of chunk per queue
					$c_list = array();
					$list = dba_search(
						_DB_PREF_ . '_tblSMSOutgoing_queue',
						'id, queue_code',
						array(
							'flag' => '0'
						)
					);
					foreach ( $list as $db_row ) {
						$c_datetime_scheduled = strtotime($db_row['datetime_scheduled']);
						if ($c_datetime_scheduled <= strtotime(core_get_datetime())) {
							$c_list[] = $db_row;
						}
					}

					$list = array();
					$sendsmsd_queue_count = (int) $core_config['sendsmsd_queue'];
					if ($sendsmsd_queue_count > 0) {
						for ($i = 0; $i < $sendsmsd_queue_count; $i++) {
							if ($c_list[$i]) {
								$list[] = $c_list[$i];
							}
						}
					} else {
						$list = $c_list;
					}

					foreach ( $list as $db_row ) {
						// $db_row['queue_code'] = queue code
						// $db_row['queue_count'] = number of entries in a queue
						// $db_row['sms_count'] = number of SMS in an entry
						$num = 0;
						$db_query2 = "SELECT id FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst WHERE queue_id='" . $db_row['id'] . "'";
						$db_result2 = dba_query($db_query2);
						while ($db_row2 = dba_fetch_array($db_result2)) {
							$num++;
							if ($chunk = floor($num / $core_config['sendsmsd_chunk_size'])) {
								$db_query3 = "UPDATE " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst SET chunk='" . $chunk . "' WHERE id='" . $db_row2['id'] . "'";
								$db_result3 = dba_query($db_query3);
							}
						}

						if ($num > 0) {
							// destination found, update queue to process step
							sendsms_queue_update(
								$db_row['queue_code'],
								array(
									'flag' => 3
								)
							);
						} else {
							// no destination found, something's not right with the queue, mark it as done (flag 1)
							if (
								sendsms_queue_update(
									$db_row['queue_code'],
									array(
										'flag' => 1
									)
								)
							) {
								_log('enforce init finish queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
							} else {
								_log('fail to enforce init finish queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
							}
						}
					}

					// process step
					$queue = array();

					$list = dba_search(_DB_PREF_ . '_tblSMSOutgoing_queue', 'id, queue_code', array(
						'flag' => '3'
					), [], $extras);
					foreach ( $list as $db_row ) {
						// get chunks
						$c_chunk_found = 0;
						$db_query2 = "SELECT chunk FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst WHERE queue_id='" . $db_row['id'] . "' AND flag='0' GROUP BY chunk LIMIT " . $core_config['sendsmsd_chunk'];
						$db_result2 = dba_query($db_query2);
						while ($db_row2 = dba_fetch_array($db_result2)) {
							$c_chunk = (int) $db_row2['chunk'];
							$queue[] = 'Q_' . $db_row['queue_code'] . '_' . $c_chunk;
							$c_chunk_found++;
						}

						if ($c_chunk_found < 1) {
							// no chunk found, something's not right with the queue, mark it as done (flag 1)
							if (
								sendsms_queue_update(
									$db_row['queue_code'],
									array(
										'flag' => 1
									)
								)
							) {
								_log('enforce finish process queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
							} else {
								_log('fail to enforce finish process queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
							}
						}
					}

					// execute step
					$queue = array_unique($queue);
					if (count($queue) > 0) {
						foreach ( $queue as $q ) {
							$is_sending = (playsmsd_pid_get($q) ? true : false);
							if (!$is_sending) {
								$RUN_THIS = "nohup $PLAYSMSD_COMMAND sendqueue once $q >/dev/null 2>&1 &";
								echo $COMMAND . " execute: " . $RUN_THIS . "" . PHP_EOL;
								shell_exec($RUN_THIS);
							}
						}
					}
					break;

				default:
					$DAEMON_LOOPING = false;
			}

			// END OF MAIN LOOP BLOCK


			//echo $COMMAND . " end time:" . time() . "" . PHP_EOL;


			sleep($DAEMON_SLEEP);

			// empty buffer, yes doubled :)
			ob_end_flush();
			ob_end_flush();
		}

		// while true
	}
}

