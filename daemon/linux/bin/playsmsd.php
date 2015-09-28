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

error_reporting(0);

// functions


/**
 * Get pid for certain playsmsd process
 *
 * @param string $process
 *        process name
 * @return integer PID
 */
function playsmsd_pid_get($process) {
	global $PLAYSMSD_CONF;
	
	return trim(shell_exec("ps -eo pid,command | grep '" . $PLAYSMSD_CONF . "' | grep '" . $process . "' | grep -v grep | sed -e 's/^ *//' -e 's/ *$//' | cut -d' ' -f1 | tr '\n' ' '"));
}

/**
 * Get pids for all playsmsd main process
 *
 * @return array PIDs
 */
function playsmsd_pids() {
	$pids['schedule'] = playsmsd_pid_get('schedule');
	$pids['dlrssmsd'] = playsmsd_pid_get('dlrssmsd');
	$pids['recvsmsd'] = playsmsd_pid_get('recvsmsd');
	$pids['sendsmsd'] = playsmsd_pid_get('sendsmsd');
	return $pids;
}

/**
 * Show pids
 */
function playsmsd_pids_show() {
	$pids = playsmsd_pids();
	echo "schedule at pid " . $pids['schedule'] . "\n";
	echo "dlrssmsd at pid " . $pids['dlrssmsd'] . "\n";
	echo "recvsmsd at pid " . $pids['recvsmsd'] . "\n";
	echo "sendsmsd at pid " . $pids['sendsmsd'] . "\n";
}

/**
 * Check whether or not playsmsd processes are running
 *
 * @return boolean TRUE if all processes are running
 */
function playsmsd_isrunning() {
	$pids = playsmsd_pids();
	foreach ($pids as $pid) {
		if ($pid) {
			$isrunning = TRUE;
		} else {
			$isrunning = FALSE;
			break;
		}
	}
	return $isrunning;
}

/**
 * Start playsmsd scripts
 */
function playsmsd_start() {
	global $PLAYSMSD_COMMAND;
	
	if (playsmsd_isrunning()) {
		echo "playsmsd is already running\n";
		playsmsd_pids_show();
		exit();
	}
	
	// stop all daemons
	shell_exec("$PLAYSMSD_COMMAND stop >/dev/null 2>&1");
	sleep(2);
	
	// run playsmsd services
	shell_exec("nohup $PLAYSMSD_COMMAND schedule >/dev/null 2>&1 &");
	shell_exec("nohup $PLAYSMSD_COMMAND dlrssmsd >/dev/null 2>&1 &");
	shell_exec("nohup $PLAYSMSD_COMMAND recvsmsd >/dev/null 2>&1 &");
	shell_exec("nohup $PLAYSMSD_COMMAND sendsmsd >/dev/null 2>&1 &");
	
	if (playsmsd_isrunning()) {
		echo "playsmsd has been started\n";
		playsmsd_pids_show();
	} else {
		echo "Unable to start playsmsd\n";
	}
}

/**
 * Stop playsmsd scripts
 */
function playsmsd_stop() {
	$pids = playsmsd_pids();
	foreach ($pids as $key => $val) {
		if ($key && $val) {
			echo $key . " at pid " . $val . " will be killed..\n";
			shell_exec("kill " . $val . " >/dev/null 2>&1");
		}
	}
	
	if (playsmsd_isrunning()) {
		echo "Unable to stop playsmsd\n";
		playsmsd_pids_show();
	} else {
		echo "playsmsd has been stopped\n";
	}
}

/**
 * Stop child scripts
 */
function playsmsd_stop_childs() {
	$pids['sendqueue'] = playsmsd_pid_get('sendqueue');
	foreach ($pids as $key => $val) {
		if ($key && $val) {
			echo $key . " at pid " . $val . " will be killed..\n";
			shell_exec("kill " . $val . " >/dev/null 2>&1");
		}
	}
}

/**
 * Check variables and states of playsmsd
 *
 * @param boolean $json
 *        TRUE for json output
 * @return string
 */
function playsmsd_check($json) {
	global $PLAYSMSD_CONF, $DAEMON_SLEEP, $ERROR_REPORTING;
	global $PLAYSMS_INSTALL_PATH, $PLAYSMS_LIB_PATH, $PLAYSMS_DAEMON_PATH, $PLAYSMS_LOG_PATH;
	
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
		echo json_encode($data);
	} else {
		foreach ($data as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $k => $v) {
					echo $key . " " . $k . " = " . $v . "\n";
				}
			} else {
				echo $key . " = " . $val . "\n";
			}
		}
	}
}

/**
 * View log
 *
 * @param string $debug_file
 *        Save log to debug file
 */
function playsmsd_log($debug_file = '') {
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

// check if 1st argv is playsmsd.conf path and command in 2nd argv is not empty
if (file_exists($argument[1]) && $argument[2]) {
	$p = pathinfo($argument[1]);
	if ($p['extension'] == 'conf') {
		$PLAYSMSD_CONF = $argument[1];
		array_shift($argument);
	}
}

$ini = array();

$ini_files = array(
	$PLAYSMSD_CONF,
	'./playsmsd.conf',
	'~/playsmsd.conf',
	'~/etc/playsmsd.conf',
	'/etc/playsmsd.conf',
	'/usr/local/etc/playsmsd.conf',
	'/usr/bin/playsmsd.conf',
	'/usr/local/bin/playsmsd.conf' 
);

$continue = FALSE;
foreach ($ini_files as $PLAYSMSD_CONF) {
	if ($PLAYSMSD_CONF && file_exists($PLAYSMSD_CONF)) {
		$ini = @parse_ini_file($PLAYSMSD_CONF);
		if ($ini['PLAYSMS_PATH'] && $ini['PLAYSMS_BIN'] && $ini['PLAYSMS_LOG']) {
			$continue = TRUE;
			break;
		}
	}
}

if (!$continue) {
	echo "Unable to find playsmsd.conf\n";
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
$ERROR_REPORTING = (isset($ini['ERROR_REPORTING']) ? $ini['ERROR_REPORTING'] : 'E_ALL ^ (E_NOTICE | E_WARNING)');

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
		break;
	
	case 'stop':
		
		// stop playsmsd services
		playsmsd_stop();
		
		// stop playsmsd child scripts
		playsmsd_stop_childs();
		
		exit();
		break;
	
	case 'restart':
		
		// stop, wait for 2 seconds and then start
		playsmsd_stop();
		sleep(2);
		playsmsd_start();
		
		exit();
		break;
	
	case 'status':
		
		if (playsmsd_isrunning()) {
			echo "playsmsd is running\n";
			playsmsd_pids_show();
		} else {
			echo "playsmsd is not running\n";
		}
		
		exit();
		break;
	
	case 'check':
		
		// non-JSON output
		playsmsd_check(FALSE);
		
		exit();
		break;
	
	case 'check_json':
		
		// JSON output
		playsmsd_check(TRUE);
		
		exit();
		break;
	
	case 'log':
		
		// View log
		$debug_file = ($argument[2] ? $argument[2] : '');
		playsmsd_log($debug_file);
		
		exit();
		break;
}

if (!$COMMAND) {
	echo "Usage: playsmsd <start|stop|restart|status|check|check_json|log|version>\n";
	exit();
}

if (file_exists($PLAYSMS_INSTALL_PATH)) {
	chdir($PLAYSMS_INSTALL_PATH);
	
	// mark this process as a DAEMON_PROCESS
	$DAEMON_PROCESS = true;
	
	$continue = FALSE;
	if (file_exists('init.php')) {
		include 'init.php';
		$fn = $core_config['apps_path']['libs'] . '/function.php';
		if ($core_config['daemon_process'] && file_exists($fn)) {
			include $fn;
			if ($core_config['apps_path']['incs']) {
				$continue = TRUE;
			}
		}
	}
	
	if ($continue && $COMMAND == 'version') {
		echo core_get_version() . PHP_EOL;
		
		exit();
	} else if ($continue && $LOOP_FLAG == 'once') {
		
		// execute one time only
		

		// MAIN ONCE BLOCK
		

		//echo $COMMAND . " start time:" . mktime() . "\n";
		

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
		

		//echo $COMMAND . " end time:" . mktime() . "\n";
		

		exit();
	} else if ($continue && $LOOP_FLAG == 'loop') {
		
		// execute in a loop
		

		$DAEMON_LOOPING = TRUE;
		
		while ($DAEMON_LOOPING) {
			
			//echo $COMMAND . " start time:" . mktime() . "\n";
			

			// re-include init.php on every 'while' to get the most updated configurations
			include 'init.php';
			
			// MAIN LOOP BLOCK
			

			switch ($COMMAND) {
				case 'schedule':
					playsmsd();
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
					$extras = '';
					if ((int) $core_config['sendsmsd_queue'] > 0) {
						$extras = array(
							'LIMIT' => (int) $core_config['sendsmsd_queue'] 
						);
					}
					$list = dba_search(_DB_PREF_ . '_tblSMSOutgoing_queue', 'id', array(
						'flag' => '0' 
					), '', $extras);
					foreach ($list as $db_row) {
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
						
						// update queue to process step
						$db_query4 = "UPDATE " . _DB_PREF_ . "_tblSMSOutgoing_queue SET flag='3' WHERE id='" . $db_row['id'] . "'";
						$db_result4 = dba_query($db_query4);
					}
					
					// process step
					$queue = array();
					
					$list = dba_search(_DB_PREF_ . '_tblSMSOutgoing_queue', 'id, queue_code', array(
						'flag' => '3' 
					), '', $extras);
					foreach ($list as $db_row) {
						// get chunks
						$db_query2 = "SELECT chunk FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst WHERE queue_id='" . $db_row['id'] . "' AND flag='0' GROUP BY chunk LIMIT " . $core_config['sendsmsd_chunk'];
						$db_result2 = dba_query($db_query2);
						while ($db_row2 = dba_fetch_array($db_result2)) {
							$c_chunk = (int) $db_row2['chunk'];
							$queue[] = 'Q_' . $db_row['queue_code'] . '_' . $c_chunk;
						}
					}
					
					// execute step
					$queue = array_unique($queue);
					if (count($queue) > 0) {
						foreach ($queue as $q) {
							$is_sending = (playsmsd_pid_get($q) ? TRUE : FALSE);
							if (!$is_sending) {
								$RUN_THIS = "nohup $PLAYSMSD_COMMAND sendqueue once $q >/dev/null 2>&1 &";
								echo $COMMAND . " execute: " . $RUN_THIS . "\n";
								shell_exec($RUN_THIS);
							}
						}
					}
					break;
				
				default :
					$DAEMON_LOOPING = FALSE;
			}
			
			// END OF MAIN LOOP BLOCK
			

			//echo $COMMAND . " end time:" . mktime() . "\n";
			

			sleep($DAEMON_SLEEP);
			
			// empty buffer, yes doubled :)
			ob_end_flush();
			ob_end_flush();
		}
		
		// while TRUE
	}
}
