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
defined('_SECURE_') or die('Forbidden');

/**
 * Get pid for certain playsmsd process
 *
 * @param string $process
 *        process name
 * @return array PIDs
 */
function playsmsd_pid_get($process_marker1 = '', $process_marker2 = '')
{
	$returns = array();

	$check_process = '';
	if ($process_marker1) {
		$check_process .= '|grep ' . $process_marker1;
	}
	if ($process_marker2) {
		$check_process .= '|grep ' . $process_marker2;
	}

	$pids = trim(shell_exec('ps -eo pid,command|grep playsmsd|grep _fork_' . $check_process . '|grep -v grep|sed -e "s/^[[:space:]]*//"|cut -d" " -f1'));
	$pids = explode("\n", $pids);
	foreach ( $pids as $pid ) {
		if ($pid) {
			$returns[] = $pid;
		}
	}

	return $returns;
}

/**
 * Get pids for all playsmsd main process
 *
 * @return array PIDs
 */
function playsmsd_pids()
{
	$pids = array();

	$services = playsmsd_services();
	foreach ( $services as $service ) {
		$service_pids = playsmsd_pid_get($service);
		foreach ( $service_pids as $pid ) {
			$pids[$service][] = $pid;
		}
	}

	return $pids;
}

/**
 * Show pids
 */
function playsmsd_pids_show()
{
	$pids = playsmsd_pids();
	foreach ( $pids as $service_name => $service_pids ) {
		foreach ( $service_pids as $pid ) {
			if ($pid) {
				echo $service_name . " at pid " . $pid . "\n";
			}
		}
	}
}

/**
 * Check whether or not all playsmsd services are running
 *
 * @return boolean TRUE if all processes are running
 */
function playsmsd_allrunning()
{
	$run = array();

	$pids = playsmsd_pids();
	foreach ( $pids as $service_name => $service_pids ) {
		foreach ( $service_pids as $pid ) {
			$run[$service_name] = false;
			if ($pid) {
				$run[$service_name] = true;

				continue;
			}
		}
	}

	$all_running = false;
	if ($pids) {
		$all_running = true;
		foreach ( $pids as $service_name => $service_pids ) {
			if (!$run[$service_name]) {
				$all_running = false;
			}
		}
	}

	return $all_running;
}

/**
 * Check whether or not all playsmsd services are not running
 *
 * @return boolean TRUE if no services are running
 */
function playsmsd_allstopped()
{
	$run = array();

	$pids = playsmsd_pids();
	foreach ( $pids as $service_name => $service_pids ) {
		foreach ( $service_pids as $pid ) {
			$run[$service_name] = false;
			if ($pid) {
				$run[$service_name] = true;

				continue;
			}
		}
	}

	$all_stopped = true;
	foreach ( $pids as $service_name => $service_pids ) {
		if ($run[$service_name]) {
			$all_stopped = false;
		}
	}

	return $all_stopped;
}

/**
 * Start playsmsd scripts
 */
function playsmsd_start()
{
	if (playsmsd_allrunning()) {
		echo "playsmsd is already running\n";
		playsmsd_pids_show();

		exit();
	}

	// stop all daemons - suppress stop message, prevent printed double
	ob_start();
	playsmsd_stop();
	ob_end_clean();
	sleep(1);

	// run playsmsd services
	$services = playsmsd_services();
	foreach ( $services as $service ) {
		$pids[$service] = playsmsd_run_loop($service);
	}

	if (playsmsd_allrunning()) {
		echo "playsmsd has been started\n";
		playsmsd_pids_show();
	} else {
		echo "Unable to start playsmsd\n";
	}
}

/**
 * Stop playsmsd scripts
 * 
 * @return boolean TRUE if all services stopped
 */
function playsmsd_stop()
{
	$pids = playsmsd_pids();
	foreach ( $pids as $service_name => $service_pids ) {
		foreach ( $service_pids as $pid ) {
			if ($pid) {
				echo $service_name . " at pid " . $pid . " will be killed..\n";
				shell_exec("kill " . $pid . " >/dev/null 2>&1");
			}
		}
	}

	$pids = playsmsd_pid_get();
	foreach ( $pids as $pid ) {
		if ($pid) {
			echo "subprocess at pid " . $pid . " will be killed..\n";
			shell_exec("kill " . $pid . " >/dev/null 2>&1");
		}
	}

	if (playsmsd_allstopped()) {
		echo "playsmsd has been stopped\n";

		return true;
	} else {
		echo "Unable to stop playsmsd\n";
		playsmsd_pids_show();

		return false;
	}
}

/**
 * Check variables and states of playsmsd
 *
 * @param boolean $json
 *        TRUE for json output
 */
function playsmsd_check($json)
{
	global $core_config;

	$data = array(
		'PLAYSMS_WEB' => $core_config['apps_path']['base'],
		'PLAYSMS_STR' => $core_config['apps_path']['storage'],
		'PLAYSMS_LOG' => $core_config['apps_path']['logs'],
		'PLAYSMS_BIN' => _PLAYSMSD_,
		'IS_RUNNING' => playsmsd_allrunning(),
		'PIDS' => playsmsd_pids(),
	);

	if ($json) {
		echo json_encode($data);
	} else {
		foreach ( $data as $key => $val ) {
			if ($key == 'PIDS' && is_array($val)) {
				$pids = $val;
				foreach ( $pids as $service_name => $service_pids ) {
					echo "PIDS " . $service_name . " = ";
					foreach ( $service_pids as $pid ) {
						echo $pid . " ";
					}
					echo "\n";
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
function playsmsd_log($debug_file = '')
{
	global $core_config;

	$log = $core_config['apps_path']['logs'] . '/' . $core_config['logfile'];
	if (file_exists($log) && is_writable($log)) {

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
	} else {
		echo "playSMS log file " . $log . " not found or unwritable\n";
	}
}

/**
 * Get playSMS daemon services
 *
 * @return array Service names
 */
function playsmsd_services()
{
	global $plugin_config;

	$services = array();

	if (isset($plugin_config['core']['playsmsd']['services'])) {
		$services = $plugin_config['core']['playsmsd']['services'];
	}

	return $services;
}

function playsmsd_hook_playsmsd_loop($command, $command_param)
{
	if (!($command == 'schedule' || $command == 'starterd')) {

		return;
	}

	if ($command == 'schedule') {
		playsmsd();
	}

	if ($command == 'starterd') {
		$list = playsmsd_registered();

		foreach ( $list as $s ) {
			$pid = playsmsd_run($s['run_type'], $s['command'], $s['param']);
			if ($pid) {
				$db_query = "UPDATE " . _DB_PREF_ . "_tblPlaysmsd SET flag_run=1, start='" . core_get_datetime() . "', pid=? WHERE id=?";
				if (dba_affected_rows($db_query, [$pid, $s['id']])) {
					_log('run type:' . $s['run_type'] . ' command:' . $s['command'] . ' param:' . $s['param'], 3, 'playsmsd_hook_playsmsd_loop');
				} else {
					_log('fail to mark run type:' . $s['run_type'] . ' command:' . $s['command'] . ' param:' . $s['param'], 3, 'playsmsd_hook_playsmsd_loop');
				}
			} else {
				_log('fail to run type:' . $s['run_type'] . ' command:' . $s['command'] . ' param:' . $s['param'], 3, 'playsmsd_hook_playsmsd_loop');
			}
		}
	}
}

function playsmsd()
{

	// plugin feature
	core_call_hook();

	// plugin gateway
	$smscs = gateway_getall_smsc_names();
	foreach ( $smscs as $smsc ) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateways[] = $smsc_data['gateway'];
	}
	if (is_array($gateways)) {
		$gateways = array_unique($gateways);
		foreach ( $gateways as $gateway ) {
			core_hook($gateway, 'playsmsd');
		}
	}

	// plugin themes
	core_hook(core_themes_get(), 'playsmsd');
}

function playsmsd_loop($command, $command_param)
{
	//_log("start command:" . $command . " param:" . $command_param, 3, "playsmsd_loop");

	// plugin core & feature
	core_call_hook();

	// plugin gateway
	$smscs = gateway_getall_smsc_names();
	foreach ( $smscs as $smsc ) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateways[] = $smsc_data['gateway'];
	}
	if (is_array($gateways)) {
		$gateways = array_unique($gateways);
		foreach ( $gateways as $gateway ) {
			core_hook(
				$gateway,
				'playsmsd_loop',
				array(
					$command,
					$command_param,
				)
			);
		}
	}

	// plugin themes
	core_hook(
		core_themes_get(),
		'playsmsd_loop',
		array(
			$command,
			$command_param,
		)
	);

	//_log("finish command:" . $command . " param:" . $command_param, 3, "playsmsd_loop");
}

function playsmsd_once($command, $command_param)
{
	_log("start command:" . $command . " param:" . $command_param, 3, "playsmsd_once");

	// plugin core & feature
	core_call_hook();

	// plugin gateway
	$smscs = gateway_getall_smsc_names();
	foreach ( $smscs as $smsc ) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateways[] = $smsc_data['gateway'];
	}
	if (is_array($gateways)) {
		$gateways = array_unique($gateways);
		foreach ( $gateways as $gateway ) {
			core_hook(
				$gateway,
				'playsmsd_once',
				array(
					$command,
					$command_param,
				)
			);
		}
	}

	// plugin themes
	core_hook(
		core_themes_get(),
		'playsmsd_once',
		array(
			$command,
			$command_param,
		)
	);

	_log("finish command:" . $command . " param:" . $command_param, 3, "playsmsd_once");
}


// playsmsd run services
// -----------------------------------------------------------------------------

function playsmsd_run($run_type = 'once', $command, $command_param = '')
{
	global $core_config;

	if (isset($run_type) && isset($command)) {

		// check if command is running
		$is_running = (playsmsd_pid_get($command, $command_param) ? TRUE : FALSE);

		// prevent command runs more than once
		if ($is_running) {

			return false;
		}

		if ($core_config['daemon_process']) {
			$playsmsd = _PLAYSMSD_;
		} else {
			$fn1 = realpath($core_config['apps_path']['bin'] . '/playsmsd');
			$fn2 = realpath($core_config['apps_path']['bin'] . '/playsmsd.php');
			if (file_exists($fn1)) {
				$playsmsd = $fn1;
			} else if (file_exists($fn2)) {
				$playsmsd = $fn2;
			} else {
				$playsmsd = '';
			}
		}
		if (!($playsmsd && file_exists(($playsmsd)))) {
			_log('daemon not found bin:' . $playsmsd . ' run_type:' . $run_type . ' command:' . $command . ' param:' . $command_param, 2, 'playsmsd_run');

			return false;
		}

		// fork it
		putenv("PLAYSMS_WEB=" . _APPS_PATH_BASE_);
		$RUN = 'nohup ionice -c3 nice -n19 php -q ' . $playsmsd . ' _fork_ ' . $command . ' ' . $run_type . ' ' . $command_param . ' >/dev/null 2>&1 & printf "%u" $!';
		$pid = shell_exec($RUN);

		// log it
		if ($pid) {
			_log('run type:' . $run_type . ' command:' . $command . ' param:' . $command_param . ' pid:' . $pid, 3, 'playsmsd_run');
		} else {
			_log('fail to run type:' . $run_type . ' command:' . $command . ' param:' . $command_param . ' pid:' . $pid, 2, 'playsmsd_run');
		}

		return $pid;
	} else {

		return false;
	}
}

function playsmsd_run_once($command, $command_param = '')
{
	return playsmsd_run('once', $command, $command_param);
}

function playsmsd_run_loop($command, $command_param = '')
{
	return playsmsd_run('loop', $command, $command_param);
}


// playsmsd register services
// -----------------------------------------------------------------------------

function playsmsd_registered()
{
	$returns = array();

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblPlaysmsd WHERE flag_run=0 AND flag_deleted=0";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$returns[] = $db_row;
	}

	return $returns;
}

function playsmsd_register($run_type, $command, $command_param = '')
{
	$run_type = strtolower(trim($run_type));
	if (!($run_type == 'once' || $run_type == 'loop')) {
		_log('unknown run type:' . $run_type . ' command:' . $command . ' param:' . $command_param, 2, 'playsmsd_register');

		return false;
	}

	$c_command = core_sanitize_username(trim($command));
	$command = trim(preg_replace('/[^a-z\d._-]/i', '', $c_command));
	if (!($command && $command == $c_command)) {
		_log('unclean command found type:' . $run_type . ' command:' . $command . ' param:' . $command_param, 2, 'playsmsd_register');

		return false;
	}

	$c_command_param = core_sanitize_username(trim($command_param));
	$command_param = trim(preg_replace('/[^a-z\d._-]/i', '', $c_command_param));
	if (!($command_param && $command_param == $c_command_param)) {
		_log('unclean param found type:' . $run_type . ' command:' . $command . ' param:' . $command_param, 2, 'playsmsd_register');

		return false;
	}

	if (!$_SESSION['uid']) {
		_log('no valid auth found type:' . $run_type . ' command:' . $command . ' param:' . $command_param, 2, 'playsmsd_register');

		return false;
	}

	$db_query = "SELECT id FROM " . _DB_PREF_ . "_tblPlaysmsd WHERE run_type=? AND command=? AND param=? AND flag_run=0 AND flag_deleted=0";
	$db_result = dba_query($db_query, [$run_type, $command, $command_param]);
	if ($db_row = dba_fetch_array($db_result)) {
		_log('fail to register service already registered id:' . $db_row['id'] . ' uid:' . $_SESSION['uid'] . ' command:' . $command . ' param:' . $command_param, 2, 'playsmsd_register');

		return false;
	} else {
		$db_query = "
			INSERT INTO " . _DB_PREF_ . "_tblPlaysmsd 
			(uid, run_type, command, param, created, flag_run, flag_deleted)
			VALUES
			(?, ?, ?, ?, '" . core_get_datetime() . "', 0, 0)
		";
		//_log('debug db_query:' . trim($db_query), 2, 'playsmsd_register');
		if ($db_result = dba_insert_id($db_query, [$_SESSION['uid'], $run_type, $command, $command_param])) {
			_log('service registered id:' . $db_row['id'] . ' uid:' . $_SESSION['uid'] . ' type:' . $run_type . ' command:' . $command . ' param:' . $command_param, 2, 'playsmsd_register');

			return true;
		} else {
			_log('fail to add service id:' . $db_row['id'] . ' uid:' . $_SESSION['uid'] . ' type:' . $run_type . ' command:' . $command . ' param:' . $command_param, 2, 'playsmsd_register');

			return false;
		}
	}
}

function playsmsd_register_once($command, $command_param = '')
{
	return playsmsd_register('once', $command, $command_param);
}

function playsmsd_register_loop($command, $command_param = '')
{
	return playsmsd_register('loop', $command, $command_param);
}