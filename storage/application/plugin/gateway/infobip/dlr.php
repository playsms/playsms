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
error_reporting(0);

if (!$called_from_hook_call) {
	chdir("../../../");

	// ignore CSRF
	$core_config['init']['ignore_csrf'] = TRUE;

	include "init.php";
	include $core_config['apps_path']['libs'] . "/function.php";
	chdir("plugin/gateway/infobip/");
	$requests = $_REQUEST;
}

$remote_addr = _REMOTE_ADDR_;

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
_log("dlr request: " . $xml, 3, "infobip dlr");

preg_match_all('/id=\"([0-9]+)\"/', $xml, $result);
$apimsgid = $result[1][0];
_log("apimsgid: " . $apimsgid, 3, "infobip dlr");

if (preg_match_all('/status=\"([A-Z]+)\"/', $xml, $result)) {
	$status = $result[1][0];
}

$db_query = "SELECT smslog_id FROM " . _DB_PREF_ . "_gatewayInfobip_apidata WHERE apimsgid=?";
$db_result = dba_query($db_query, [$apimsgid]);
$db_row = dba_fetch_array($db_result);
$smslog_id = $db_row['smslog_id'];

$db_query = "SELECT uid FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE smslog_id=?";
$db_result = dba_query($db_query, [$smslog_id]);
$db_row = dba_fetch_array($db_result);
$uid = $db_row['uid'];

_log("addr:" . $remote_addr . " host:" . $remote_host . " type:" . $status . " smslog_id:" . $smslog_id . " uid:" . $uid, 2, "infobip dlr");

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
	$db_query = "UPDATE " . _DB_PREF_ . "_gatewayInfobip_apidata SET c_timestamp='" . time() . "', status=? WHERE smslog_id=?";
	dba_affected_rows($db_query, [$status, $smslog_id]);

	// } else {
	// $db_query = "INSERT INTO "._DB_PREF_."_gatewayKannel_dlr (smslog_id,kannel_dlr_type) VALUES ('$smslog_id','$type')";
	// $db_result = dba_query($db_query);
	// }


}
