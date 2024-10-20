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

// load callback init
if (!(isset($PLAYSMS_INIT_SKIP) && $PLAYSMS_INIT_SKIP === true) && is_file('../common/callback_init.php')) {
	include '../common/callback_init.php';
}

// get SMS data from request
$remote_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$status = isset($_REQUEST['message_status']) ? (int) $_REQUEST['message_status'] : 0;
$sms_datetime = core_get_datetime();
$sms_sender = isset($_REQUEST['from']) ? core_sanitize_mobile($_REQUEST['from']) : '';
$sms_receiver = isset($_REQUEST['to']) ? core_sanitize_mobile($_REQUEST['to']) : '';
$message = isset($_REQUEST['message']) ? $_REQUEST['message'] : '';
$smsc = isset($_REQUEST['smsc']) ? $_REQUEST['smsc'] : '';
$authcode = isset($_REQUEST['authcode']) && trim($_REQUEST['authcode']) ? trim($_REQUEST['authcode']) : '';

// validate authcode
if (!gateway_callback_auth('generic', 'callback_authcode', $authcode, $smsc)) {
	_log("error auth authcode:" . $authcode . " smsc:" . $smsc . " remote_id:" . $remote_id . " from:" . $sms_sender . " to:" . $sms_receiver . " content:[" . $message . "]", 2, "generic callback");

	ob_end_clean();
	echo 'ERROR AUTH ' . _PID_;
	exit();
}

// validate requests must be coming from callback servers
if (!gateway_callback_server('generic', 'callback_server', $smsc)) {
	_log("error forbidden authcode:" . $authcode . " smsc:" . $smsc . " remote_id:" . $remote_id . " from:" . $sms_sender . " to:" . $sms_receiver . " content:[" . $message . "]", 2, "generic callback");

	ob_end_clean();
	echo 'ERROR FORBIDDEN ' . _PID_;
	exit();
}

// handle DLR
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
					$p_status = 1; // sent
					break;
				case 3:
					$p_status = 3; // delivered
					break;
				default:
					$p_status = 2; // failed
					break;
			}
			_log("dlr uid:" . $uid . " smslog_id:" . $smslog_id . " remote_id:" . $remote_id . " status:" . $status, 2, "generic callback");

			dlr($smslog_id, $uid, $p_status);

			ob_end_clean();
			echo 'OK ' . _PID_;
			exit();
		}
	}
}

// handle incoming SMS
if ($remote_id && $message) {
	_log("incoming smsc:" . $smsc . " remote_id:" . $remote_id . " from:" . $sms_sender . " to:" . $sms_receiver . " content:[" . $message . "]", 2, "generic callback");

	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);

	ob_end_clean();
	echo 'OK ' . _PID_;
	exit();
}
