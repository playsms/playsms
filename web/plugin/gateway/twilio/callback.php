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
define('_CALLBACK_GATEWAY_NAME_', 'twilio');
define('_CALLBACK_GATEWAY_LOG_MARKER_', _CALLBACK_GATEWAY_NAME_ . ' callback');

error_reporting(0);

// load callback init
if (!(isset($PLAYSMS_INIT_SKIP) && $PLAYSMS_INIT_SKIP === true) && is_file('../common/callback_init.php')) {
	include '../common/callback_init.php';
}

$remote_smslog_id = $requests['SmsSid'];
$status = $requests['SmsStatus'];

// delivery receipt
if ($remote_smslog_id && $status && ($status != 'received')) {
	$db_query = "SELECT local_smslog_id FROM " . _DB_PREF_ . "_gatewayTwilio WHERE remote_smslog_id='$remote_smslog_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$smslog_id = $db_row['local_smslog_id'];
	if ($smslog_id) {
		$data = sendsms_get_sms($smslog_id);
		$uid = $data['uid'];
		$p_status = $data['p_status'];
		switch ($status) {
			case "sent":
				$p_status = 1;
				break; // delivered
			default:
				$p_status = 2;
				break; // failed
		}
		_log("dlr uid:" . $uid . " smslog_id:" . $smslog_id . " message_id:" . $remote_smslog_id . " status:" . $status, 2, _CALLBACK_GATEWAY_LOG_MARKER_);
		dlr($smslog_id, $uid, $p_status);
		ob_end_clean();
	}
	exit();
}

// incoming message
$sms_datetime = $core_config['datetime']['now'];
$sms_sender = $requests['From'];
$message = htmlspecialchars_decode(urldecode($requests['Body']));
$sms_receiver = $requests['To'];
$smsc = $requests['smsc'];

// ref: https://www.twilio.com/docs/api/rest/sms#list
if ($remote_smslog_id && $message && ($status == 'received')) {
	_log("incoming smsc:" . $smsc . " message_id:" . $remote_smslog_id . " s:" . $sms_sender . " d:" . $sms_receiver, 2, _CALLBACK_GATEWAY_LOG_MARKER_);
	$sms_sender = addslashes($sms_sender);
	$message = addslashes($message);
	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
}

