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

// log original request
_log($_REQUEST, 3, "jasmin callback");

// get SMS data from request
$remote_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$status = isset($_REQUEST['message_status']) ? $_REQUEST['message_status'] : '';
$sms_datetime = core_get_datetime();
$sms_sender = isset($_REQUEST['from']) ? core_sanitize_mobile($_REQUEST['from']) : '';
$sms_receiver = isset($_REQUEST['to']) ? core_sanitize_mobile($_REQUEST['to']) : '';
$message = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';

if ($remote_id && $status) {
	$smsc = isset($_REQUEST['connector']) ? $_REQUEST['connector'] : ''; // SMSC in dlr push
} else {
	$smsc = isset($_REQUEST['origin-connector']) ? $_REQUEST['origin-connector'] : ''; // SMSC in incoming SMS push
}

$authcode = isset($_REQUEST['authcode']) && trim($_REQUEST['authcode']) ? trim($_REQUEST['authcode']) : '';

// validate authcode
if (!gateway_callback_auth('jasmin', 'callback_authcode', $authcode, $smsc)) {
	_log("error auth authcode:" . $authcode . " smsc:" . $smsc . " remote_id:" . $remote_id . " from:" . $sms_sender . " to:" . $sms_receiver . " content:[" . $message . "]", 2, "jasmin callback");

	ob_end_clean();
	exit();
}

// validate _REQUEST must be coming from callback servers
if (!gateway_callback_server('jasmin', 'callback_server', $smsc)) {
	_log("error forbidden authcode:" . $authcode . " smsc:" . $smsc . " remote_id:" . $remote_id . " from:" . $sms_sender . " to:" . $sms_receiver . " content:[" . $message . "]", 2, "jasmin callback");

	ob_end_clean();
	exit();
}

// handle DLR
if ($remote_id && $status) {
	$db_query = "SELECT uid,smslog_id FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE remote_id=? AND p_status=1 AND flag_deleted=0";
	$db_result = dba_query($db_query, [$remote_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$uid = (int) $db_row['uid'];
		$smslog_id = (int) $db_row['smslog_id'];
		switch ($status) {
			case "DELIVRD":
			case "ESME_ROK":
				$p_status = 3;  // delivered
				break;
			default:
				$p_status = 2; // failed
				break;
		}
		// log it
		_log("dlr uid:" . $uid . " smslog_id:" . $smslog_id . " remote_id:" . $remote_id . " status:" . $status . " p_status:" . $p_status, 2, "jasmin callback");

		// set delivery report
		dlr($smslog_id, $uid, $p_status);

		ob_end_clean();
		echo "ACK/Jasmin";
		exit();
	}
}

// handle incoming SMS (MO)
if ($remote_id && $sms_sender && $message) {
	// log it
	_log("incoming dt:" . $sms_datetime . " from:" . $sms_sender . " to:" . $sms_receiver . " message:[" . $message . "] smsc:" . $smsc, 2, "jasmin callback");

	// save incoming SMS for further processing
	$sms_recvlog_id = recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);

	ob_end_clean();
	echo "ACK/Jasmin";
	exit();
}
