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

$remote_addr = $_SERVER['REMOTE_ADDR'];

// srosa 20100531: added var below
$remote_host = $_SERVER['HTTP_HOST'];

// srosa 20100531: changed test below to allow hostname in bearerbox_host instead of ip
// if ($remote_addr != $kannel_param['bearerbox_host'])
// if ($remote_addr != $kannel_param['bearerbox_host'] && $remote_host != $kannel_param['bearerbox_host']) {
// _log("exit remote_addr:".$remote_addr." remote_host:".$remote_host." bearerbox_host:".$kannel_param['bearerbox_host'], 2, "kannel dlr");
// exit();
// }

$xml = file_get_contents('php://input');

// file_put_contents('toto.txt', $xml, true);
_log("dlr request: " . $xml, 3, _CALLBACK_GATEWAY_LOG_MARKER_);

preg_match_all('/id=\"([0-9]+)\"/', $xml, $result);
$apimsgid = $result[1][0];
_log("apimsgid: " . $apimsgid, 3, _CALLBACK_GATEWAY_LOG_MARKER_);

if (preg_match_all('/status=\"([A-Z]+)\"/', $xml, $result)) {
	$status = $result[1][0];
}

$db_query = "SELECT smslog_id FROM " . _DB_PREF_ . "_gatewayInfobip_apidata WHERE apimsgid='$apimsgid'";
$db_result = dba_query($db_query);
$db_row = dba_fetch_array($db_result);
$smslog_id = $db_row['smslog_id'];

$db_query = "SELECT uid FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE smslog_id='$smslog_id'";
$db_result = dba_query($db_query);
$db_row = dba_fetch_array($db_result);
$uid = $db_row['uid'];

_log("addr:" . $remote_addr . " host:" . $remote_host . " type:" . $status . " smslog_id:" . $smslog_id . " uid:" . $uid, 2, _CALLBACK_GATEWAY_LOG_MARKER_);

if ($status && $smslog_id && $uid) {
	switch ($status) {
		case "DELIVERED":
			$p_status = 3;
			break;

		// delivered


		case "NOT_DELIVERED":
			$p_status = 2;
			break;

		// failed


		case "NOT_ENOUGH_CREDITS":
			$p_status = 2;
			break;

		// failed


	}

	dlr($smslog_id, $uid, $p_status);

	// log dlr
	// $db_query = "SELECT apimsgid FROM "._DB_PREF_."_gatewayInfobip_apidata WHERE smslog_id='$smslog_id'";
	// $db_result = dba_num_rows($db_query);
	// if ($db_result > 0) {
	$db_query = "UPDATE " . _DB_PREF_ . "_gatewayInfobip_apidata SET c_timestamp='" . time() . "', status='$status' WHERE smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);

	// } else {
	// $db_query = "INSERT INTO "._DB_PREF_."_gatewayKannel_dlr (smslog_id,kannel_dlr_type) VALUES ('$smslog_id','$type')";
	// $db_result = dba_query($db_query);
	// }


}
