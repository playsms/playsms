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

// handle incoming
$cb_timestamp = isset($requests['datetime']) && strtotime($requests['datetime']) ? strtotime($requests['datetime']) : time();
$datetime = date($datetime_format, (int) $cb_timestamp);
$sender = isset($requests['sender']) ? core_sanitize_mobile($requests['sender']) : '';
$receiver = isset($requests['receiver']) ? core_sanitize_mobile($requests['receiver']) : '';
$message = isset($requests['text']) ? $requests['text'] : '';
$smsc = isset($requests['smsc']) ? $requests['smsc'] : '';

if ($datetime && $sender && $message) {

	_log("incoming dt:" . $datetime . " from:" . $sender . " to:" . $receiver . " message:[" . $message . "]", 2, _CALLBACK_GATEWAY_LOG_MARKER_);

	// collected:
	// $datetime, $sender, $message, $receiver
	recvsms($datetime, $sender, $message, $receiver, $smsc);
}

// handle dlr
$status = $requests['status'];
$remote_id = $requests['apiMsgId'];
//$charge = $requests['charge'];

if ($status && $remote_id) {
	$db_query = "SELECT uid,smslog_id FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE remote_id=? AND p_status=1 AND flag_deleted=0";
	$db_result = dba_query($db_query, [$remote_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$uid = (int) $db_row['uid'];
		$smslog_id = (int) $db_row['smslog_id'];

		if ($uid && $smslog_id) {
			$p_status = 0;
			switch ($status) {
				case "001":
				case "002":
				case "011":
					$p_status = 0; // pending
					break;

				case "003":
				case "008":
					$p_status = 1; // sent
					break;

				case "005":
				case "006":
				case "007":
				case "009":
				case "010":
				case "012":
					$p_status = 2; // failed
					break;

				case "004":
					$p_status = 3; // delivered
					break;
			}

			// log it
			_log("dlr uid:" . $uid . " smslog_id:" . $smslog_id . " remote_id:" . $remote_id . " status:" . $status . " p_status:" . $p_status, 2, _CALLBACK_GATEWAY_LOG_MARKER_);

			dlr($smslog_id, $uid, $p_status);
		}
	}
}