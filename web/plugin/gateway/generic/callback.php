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
define('_CALLBACK_GATEWAY_NAME_', 'generic');
define('_CALLBACK_GATEWAY_LOG_MARKER_', _CALLBACK_GATEWAY_NAME_ . ' callback');

error_reporting(0);

// load callback init
if (!(isset($PLAYSMS_INIT_SKIP) && $PLAYSMS_INIT_SKIP === true) && is_file('../common/callback_init.php')) {
	include '../common/callback_init.php';
}

$remote_id = $requests['id'];

// auth first
$authcode = trim($requests['authcode']);
$data = registry_search(0, 'gateway', 'generic');
if (!($authcode && isset($data['gateway']['generic']['callback_url_authcode']) && ($authcode == $data['gateway']['generic']['callback_url_authcode']))) {
	_log("error auth authcode:" . $authcode . " smsc:" . $smsc . " remote_id:" . $remote_id . " from:" . $sms_sender . " to:" . $sms_receiver . " content:[" . $message . "]", 2, _CALLBACK_GATEWAY_LOG_MARKER_);

	ob_end_clean();
	echo 'ERROR AUTH ' . _PID_;
	exit();
}

// delivery receipt
$status = (int) $requests['message_status'];
if ($remote_id && $status) {
	$db_query = "SELECT uid,smslog_id,p_status FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE remote_id=? AND p_status=1 AND flag_deleted=0";
	$db_result = dba_query($db_query, [$remote_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$uid = (int) $db_row['uid'];
		$smslog_id = (int) $db_row['smslog_id'];
		$p_status = (int) $db_row['p_status'];
		if ($uid && $smslog_id) {
			switch ($status) {
				case 1:
					$p_status = 1;
					break; // sent
				case 3:
					$p_status = 3;
					break; // delivered
				default:
					$p_status = 2;
					break; // failed
			}
			_log("dlr uid:" . $uid . " smslog_id:" . $smslog_id . " remote_id:" . $remote_id . " status:" . $status, 2, _CALLBACK_GATEWAY_LOG_MARKER_);

			dlr($smslog_id, $uid, $p_status);

			ob_end_clean();
			echo 'OK ' . _PID_;
			exit();
		}
	}
}

// incoming message
$sms_datetime = core_get_datetime();
$sms_sender = isset($requests['from']) ? core_sanitize_mobile($requests['from']) : '';
$sms_receiver = isset($requests['to']) ? core_sanitize_mobile($requests['to']) : '';
$message = isset($requests['message']) ? $requests['message'] : '';
$smsc = isset($requests['smsc']) ? $requests['smsc'] : '';

if ($remote_id && $message) {
	_log("incoming smsc:" . $smsc . " remote_id:" . $remote_id . " from:" . $sms_sender . " to:" . $sms_receiver . " content:[" . $message . "]", 2, _CALLBACK_GATEWAY_LOG_MARKER_);

	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);

	ob_end_clean();
	echo 'OK ' . _PID_;
	exit();
}
