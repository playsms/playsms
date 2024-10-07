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
 * @param string $process process name
 * @return array PIDs
 */
function playsmsd_pid_get($process)
{
	global $core_config;

	if ($core_config['daemon']['PLAYSMSD_CONF'] && $process) {
		$pids = trim(shell_exec(sprintf(
			<<<'EOSH'
ps -eo pid,command | grep -F %s | grep -F %s | grep -vF grep | sed -e 's/^ *//' -e 's/ *$//' | cut -d' ' -f1 | tr '\n' ' '
EOSH
			,
			escapeshellarg($core_config['daemon']['PLAYSMSD_CONF'])
			,
			escapeshellarg($process)
		)));

		if ($pids) {
			$ret = preg_split('/\s+/', $pids);

			if ($ret !== false) {

				return $ret;
			}
		}
	}

	return [];
}

/**
 * Get pids for all playsmsd main process
 *
 * @return array PIDs
 */
function playsmsd_pids()
{
	global $core_config;

	$pids = [];

	foreach ( $core_config['daemon']['PLAYSMSD_DAEMONS'] as $daemon ) {
		$pids[$daemon] = playsmsd_pid_get($daemon);
	}

	return $pids;
}

/**
 * Get pids for all playsmsd's child processes
 *
 * @return array PIDs
 */
function playsmsd_child_pids()
{
	global $core_config;

	$pids = [];

	foreach ( $core_config['daemon']['PLAYSMSD_CHILD_DAEMONS'] as $daemon ) {
		$pids[$daemon] = playsmsd_pid_get($daemon);
	}

	return $pids;
}

/**
 * Show playsmsd pids
 */
function playsmsd_pids_show()
{
	global $core_config;

	$pids = playsmsd_pids();

	foreach ( $core_config['daemon']['PLAYSMSD_DAEMONS'] as $daemon ) {
		$daemon_pids = trim(implode(' ', $pids[$daemon]));
		echo $daemon . " at pid " . $daemon_pids . PHP_EOL;
	}
}

/**
 * Show playsmsd's child pids
 */
function playsmsd_child_pids_show()
{
	global $core_config;

	$pids = playsmsd_pids();

	foreach ( $core_config['daemon']['PLAYSMSD_CHILD_DAEMONS'] as $daemon ) {
		$daemon_pids = trim(implode(' ', $pids[$daemon]));
		echo $daemon . " at pid " . $daemon_pids . PHP_EOL;
	}
}

/**
 * Check whether or not playsmsd processes are running
 *
 * @return bool true if all processes are running
 */
function playsmsd_isrunning()
{
	global $core_config;

	$pids = playsmsd_pids();

	foreach ( $core_config['daemon']['PLAYSMSD_DAEMONS'] as $daemon ) {
		$daemon_pids = trim(implode(' ', $pids[$daemon]));

		// return false when theres daemon not running
		if (!$daemon_pids) {

			return false;
		}
	}

	return true;
}

/**
 * Check whether or not playsmsd's child processes are running
 *
 * @return bool true if all processes are running
 */
function playsmsd_child_isrunning()
{
	global $core_config;

	$pids = playsmsd_child_pids();

	foreach ( $core_config['daemon']['PLAYSMSD_CHILD_DAEMONS'] as $daemon ) {
		$daemon_pids = trim(implode(' ', $pids[$daemon]));

		// return false when theres daemon not running
		if (!$daemon_pids) {

			return false;
		}
	}

	return true;
}

/**
 * Start playsmsd
 */
function playsmsd_start()
{
	global $core_config;

	if (playsmsd_isrunning()) {
		echo "playsmsd is already running" . PHP_EOL;
		playsmsd_pids_show();
		exit();
	}

	// stop all daemons
	shell_exec($core_config['daemon']['PLAYSMSD_COMMAND'] . " stop >/dev/null 2>&1");
	sleep(2);

	// run playsmsd services
	foreach ( $core_config['daemon']['PLAYSMSD_DAEMONS'] as $daemon ) {
		shell_exec("nohup " . $core_config['daemon']['PLAYSMSD_COMMAND'] . " " . escapeshellarg($daemon) . " >/dev/null 2>&1 &");
	}

	if (playsmsd_isrunning()) {
		echo "playsmsd has been started" . PHP_EOL;
		playsmsd_pids_show();
	} else {
		echo "Unable to start playsmsd" . PHP_EOL;
	}
}

/**
 * Stop playsmsd
 */
function playsmsd_stop()
{
	$pids = playsmsd_pids();

	foreach ( $pids as $key => $val ) {
		if ($key && is_array($val) && $val) {
			$daemon_pids = trim(implode(' ', $val));
			echo "$key at pid $daemon_pids will be killed..." . PHP_EOL;
			shell_exec("kill $daemon_pids >/dev/null 2>&1");
		}
	}
	sleep(2);

	if (playsmsd_isrunning()) {
		echo "Unable to stop playsmsd" . PHP_EOL;
		playsmsd_pids_show();
	} else {
		echo "playsmsd has been stopped" . PHP_EOL;
	}
}

/**
 * Stop playsmsd's child processes
 */
function playsmsd_child_stop()
{
	global $core_config;

	foreach ( $core_config['daemon']['PLAYSMSD_CHILD_DAEMONS'] as $daemon ) {
		$pids[$daemon] = playsmsd_pid_get($daemon);
	}

	foreach ( $pids as $key => $val ) {
		if ($key && is_array($val) && $val) {
			$daemon_pids = trim(implode(' ', $val));
			echo "$key at pid $daemon_pids will be killed..." . PHP_EOL;
			shell_exec("kill $daemon_pids >/dev/null 2>&1");
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
	global $core_config;

	$ret = '';

	$data = [
		'PLAYSMSD_CONF' => $core_config['daemon']['PLAYSMSD_CONF'],
		'PLAYSMS_PATH' => $core_config['daemon']['PLAYSMS_INSTALL_PATH'],
		'PLAYSMS_LIB' => $core_config['daemon']['PLAYSMS_LIB_PATH'],
		'PLAYSMS_BIN' => $core_config['daemon']['PLAYSMS_DAEMON_PATH'],
		'PLAYSMS_LOG' => $core_config['daemon']['PLAYSMS_LOG_PATH'],
		'DAEMON_SLEEP' => $core_config['daemon']['DAEMON_SLEEP'],
		'ERROR_REPORTING' => $core_config['daemon']['ERROR_REPORTING'],
		'IS_RUNNING' => playsmsd_isrunning(),
		'PIDS' => playsmsd_pids(),
		'CHILD_PIDS' => playsmsd_child_pids(),
	];

	if ($json) {
		$ret = json_encode($data);
	} else {
		foreach ( $data as $key => $val ) {
			if ($key == 'PIDS') {
				$daemon_pids = playsmsd_pids();
				foreach ( $daemon_pids as $daemon => $pids ) {
					$pids = trim(implode(' ', $pids));
					$ret .= $key . " " . $daemon . " = " . $pids . PHP_EOL;
				}
			} else if ($key == 'CHILD_PIDS') {
				$daemon_pids = playsmsd_child_pids();
				foreach ( $daemon_pids as $daemon => $pids ) {
					$pids = trim(implode(' ', $pids));
					$ret .= $key . " " . $daemon . " = " . $pids . PHP_EOL;
				}
			} else {
				if (is_array($val)) {
					foreach ( $val as $k => $v ) {
						$ret .= $key . " " . $k . " = " . $v . PHP_EOL;
					}
				} else {
					$ret .= $key . " = " . $val . PHP_EOL;
				}
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
	global $core_config;

	ob_end_clean();

	if ($debug_file && $debug_file == 'playsms.log') {
		echo "debug file cannot be named playsms.log" . PHP_EOL;

		return;
	}

	if ($debug_file && preg_match('/\/|[\.]{2}/', $debug_file)) {
		echo "debug file cannot contain paths" . PHP_EOL;

		return;
	}

	$log = $core_config['daemon']['PLAYSMS_LOG_PATH'] . '/playsms.log';

	if (is_file($log)) {

		$process = 'tail -n 0 -f ' . escapeshellarg($log) . ' 2>&1';
		if ($debug_file && $debug_file = $core_config['daemon']['PLAYSMS_LOG_PATH'] . '/' . $debug_file) {
			@shell_exec('touch ' . escapeshellarg($debug_file));
			if (is_file($debug_file)) {
				$process .= '| tee ' . escapeshellarg($debug_file);
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

/**
 * Call playsmsd hook
 * 
 * @return void
 */
function playsmsd()
{
	$gateways = [];

	// plugin feature
	core_call_hook();

	// plugin gateway
	$smscs = gateway_getall_smsc_names();
	foreach ( $smscs as $smsc ) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateways[] = $smsc_data['gateway'];
	}
	$gateways = array_unique($gateways);
	foreach ( $gateways as $gateway ) {
		core_hook($gateway, 'playsmsd');
	}

	// plugin themes
	core_hook(core_themes_get(), 'playsmsd');
}

/**
 * Call playsmsd_once hook
 * 
 * @param mixed $param
 * @return void
 */
function playsmsd_once($param)
{
	$gateways = [];

	$param = isset($param) ? $param : [];
	$param = is_array($param) ? $param : [$param];

	// plugin feature
	core_call_hook();

	// plugin gateway
	$smscs = gateway_getall_smsc_names();
	foreach ( $smscs as $smsc ) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateways[] = $smsc_data['gateway'];
	}
	$gateways = array_unique($gateways);
	foreach ( $gateways as $gateway ) {
		core_hook($gateway, 'playsmsd_once', $param);
	}

	// plugin themes
	core_hook(core_themes_get(), 'playsmsd_once', $param);
}
