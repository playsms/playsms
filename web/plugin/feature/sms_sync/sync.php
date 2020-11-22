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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

error_reporting(0);

if (!$called_from_hook_call) {
	chdir("../../../");
	
	// ignore CSRF
	$core_config['init']['ignore_csrf'] = TRUE;
	
	include "init.php";
	include $core_config['apps_path']['libs'] . "/function.php";
	chdir("plugin/feature/sms_sync/");
}

$c_uid = (int) trim($_REQUEST['uid']);
$c_secret = trim($_REQUEST['secret']);

$list = registry_search($c_uid, 'feature', 'sms_sync');
$sms_sync_secret = trim($list['feature']['sms_sync']['secret']);
$sms_sync_enable = $list['feature']['sms_sync']['enable'];

if (!$sms_sync_enable) {
	$ret = array(
		'payload' => array(
			'success' => "false",
			'error' => "feature not enabled"
		)
	);
	_p(json_encode($ret));
	exit();
}

if (!($c_uid && $c_secret && ($sms_sync_secret == $c_secret))) {
	$ret = array(
		'payload' => array(
			'success' => "false",
			'error' => "authentication failed"
		)
	);
	_p(json_encode($ret));
	exit();
}

$sms_datetime = core_display_datetime(core_get_datetime());
$username = user_uid2username($c_uid);

$message_id = trim($_REQUEST['message_id']);
$sms_sender = trim($_REQUEST['from']);
$message = trim($_REQUEST['message']);
$sms_receiver = trim($_REQUEST['sent_to']);
$sent_timestamp = trim($_REQUEST['sent_timestamp']);
$device_id = trim($_REQUEST['device_id']);

$response_success = "false"; // text, not bool
$response_error = NULL;

if ($username && $message_id && $sms_sender && $message) {
	$db_table = _DB_PREF_ . '_featureSmssysnc';
	$conditions = array(
		'uid' => $c_uid,
		'message_id' => $message_id
	);
	if (dba_isavail($db_table, $conditions, 'AND')) {
		_log("saving uid:" . $c_uid . " dt:" . $sms_datetime . " ts:" . $sent_timestamp . " message_id:" . $message_id . " s:" . $sms_sender . " m:" . $message . " r:" . $sms_receiver . " d:" . $device_id, 3, "sms_sync sync");
		
		// if keyword does not exists (keyword_isavail == TRUE)
		// then prefix the message with an @username so that it will be routed to $c_uid's inbox
		$m = explode(' ', $message);
		if (keyword_isavail($m[0])) {
			_log("forwarded to inbox uid:" . $c_uid . " message_id:" . $message_id, 3, "sms_sync sync");
			$message = "@" . $username  . " " . $message;
		}
		
		// route it
		if ($recvsms_id = recvsms($sms_datetime, $sms_sender, $message, $sms_receiver)) {
			$items = array(
				'uid' => $c_uid,
				'message_id' => $message_id,
				'recvsms_id' => $recvsms_id
			);
			dba_add($db_table, $items);
			_log("saved uid:" . $c_uid . " message_id:" . $message_id . " recvsms_id:" . $recvsms_id, 3, "sms_sync sync");
			
			$response_success = "true"; // text, not bool
		} else {
			$response_error = "fail to save uid:" . $c_uid . " message_id:" . $message_id;
			_log($response_error, 3, "sms_sync sync");
		}
	} else {
		$response_error = "duplicate message uid:" . $c_uid . " message_id:" . $message_id;
		_log($response_error, 3, "sms_sync sync");
	}
} else {
	$response_error = "incomplete request";
	_log("incomplete uid:" . $c_uid . " dt:" . $sms_datetime . " ts:" . $sent_timestamp . " message_id:" . $message_id . " s:" . $sms_sender . " m:" . $message . " r:" . $sms_receiver . " d:" . $device_id, 3, "sms_sync sync");	
}

$ret = array(
	'payload' => array(
		'success' => $response_success,
		'error' => $response_error
	)
);
_p(json_encode($ret));
