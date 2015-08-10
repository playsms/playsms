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
	chdir("plugin/gateway/jasmin/");
	$requests = $_REQUEST;
}

$log = '';
if (is_array($requests)) {
	foreach ($requests as $key => $val) {
		$log .= $key . ':' . $val . ' ';
	}
	_log("pushed " . $log, 2, "jasmin callback");
}

$remote_smslog_id = $requests['id'];
$message_status = $requests['message_status'];

// delivery receipt
if ($remote_smslog_id && $message_status) {
	$db_query = "SELECT local_smslog_id FROM " . _DB_PREF_ . "_gatewayJasmin_log WHERE remote_smslog_id='$remote_smslog_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$smslog_id = $db_row['local_smslog_id'];
	if ($smslog_id) {
		$data = sendsms_get_sms($smslog_id);
		$uid = $data['uid'];
		$p_status = $data['p_status'];
		switch ($message_status) {
			case "ESME_ROK":
				$p_status = 3;
				break; // delivered
			default :
				$p_status = 2;
				break; // failed
		}
		_log("dlr uid:" . $uid . " smslog_id:" . $smslog_id . " message_id:" . $remote_smslog_id . " status:" . $status, 2, "jasmin callback");
		dlr($smslog_id, $uid, $p_status);
		
		ob_end_clean();
		echo "ACK/Jasmin";
		exit();
	}
}

// incoming message
$sms_datetime = core_get_datetime();
$sms_sender = $requests['from'];
$message = htmlspecialchars_decode(urldecode($requests['content']));
$sms_receiver = $requests['to'];
$smsc = $requests['origin-connector'];
if ($remote_smslog_id && $message) {
	_log("incoming smsc:" . $smsc . " message_id:" . $remote_smslog_id . " from:" . $sms_sender . " to:" . $sms_receiver . " content:[" . $message . "]", 2, "jasmin callback");
	$sms_sender = addslashes($sms_sender);
	$message = addslashes($message);
	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
	
	ob_end_clean();
	echo "ACK/Jasmin";
	exit();
}
