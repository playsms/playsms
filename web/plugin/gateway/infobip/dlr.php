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

// set gateway name and log marker
define('_CALLBACK_GATEWAY_NAME_', 'infobip');
define('_CALLBACK_GATEWAY_LOG_MARKER_', _CALLBACK_GATEWAY_NAME_ . ' callback');
// -------------------- START OF CALLBACK INIT --------------------
error_reporting(0);
if (!(isset($do_not_reload_init) && $do_not_reload_init === true)) {
	if ($core_config['init']['cwd'] = getcwd()) {
		if (chdir('../../../')) {
			$core_config['init']['ignore_csrf'] = true; // ignore CSRF
			if (is_file('init.php')) { // load init && functions
				include 'init.php';
				if (isset($core_config['apps_path']['libs']) && $core_config['apps_path']['libs'] && is_file($core_config['apps_path']['libs'] . '/function.php')) {
					include $core_config['apps_path']['libs'] . '/function.php';
				}
			}
			if (!(function_exists('core_sanitize_alphanumeric') && function_exists('gateway_decide_smsc'))) { // double check
				exit();
			}
			if (!(isset($core_config['init']['cwd']) && chdir($core_config['init']['cwd']))) { // go back
				exit();
			}
		} else {
			exit();
		}
	} else {
		exit();
	}
}
$requests = $_REQUEST; // get web requests
$log = ''; // log pushed vars
if (is_array($requests)) {
	foreach ( $requests as $key => $val ) {
		$log .= $key . ':' . $val . ' ';
	}
	_log("pushed " . $log, 3, _CALLBACK_GATEWAY_LOG_MARKER_);
}
// -------------------- END OF CALLBACK INIT --------------------

$remote_addr = _REMOTE_ADDR_;
$remote_host = $_SERVER['HTTP_HOST'];

$xml = file_get_contents('php://input');

_log("dlr request: " . $xml, 3, _CALLBACK_GATEWAY_LOG_MARKER_);

$remote_id = '';
if (preg_match_all('/id=\"([0-9]+)\"/', $xml, $result)) {
	$remote_id = isset($result[1][0]) && $result[1][0] ? $result[1][0] : '';
}

$status = '';
if (preg_match_all('/status=\"([A-Z]+)\"/', $xml, $result)) {
	$status = isset($result[1][0]) && $result[1][0] ? $result[1][0] : '';
}

if (!($remote_id && $status)) {

	exit();
}

$db_query = "SELECT uid,smslog_id FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE remote_id=? AND p_status=1 AND flag_deleted=0";
$db_result = dba_query($db_query, [$remote_id]);
$db_row = dba_fetch_array($db_result);
$uid = (int) $db_row['uid'];
$smslog_id = (int) $db_row['smslog_id'];

if (!($uid && $smslog_id)) {

	exit();
}

_log("dlr addr:" . $remote_addr . " host:" . $remote_host . " remote_id:" . $remote_id . " status:" . $status . " smslog_id:" . $smslog_id . " uid:" . $uid, 2, _CALLBACK_GATEWAY_LOG_MARKER_);


switch ($status) {
	case "DELIVERED":
		$p_status = 3; // delivered
		break;

	case "NOT_DELIVERED":
		$p_status = 2; // failed
		break;

	case "NOT_ENOUGH_CREDITS":
		$p_status = 2; // failed
		break;
}

dlr($smslog_id, $uid, $p_status);
