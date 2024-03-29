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

$cb_from = $_REQUEST['sender'];
$cb_to = $_REQUEST['receiver'];
$cb_timestamp = ($_REQUEST['datetime'] ? strtotime($_REQUEST['datetime']) : time());
$cb_text = $_REQUEST['text'];
$cb_status = $_REQUEST['status'];
$cb_charge = $_REQUEST['charge'];
$cb_apimsgid = $_REQUEST['apiMsgId'];
$cb_smsc = $_REQUEST['smsc'];

/*
 * $fc = "from: $cb_from - to: $cb_to - timestamp: $cb_timestamp - text: $cb_text - status: $cb_status - charge: $cb_charge - apimsgid: $cb_apimsgid\n"; $fn = "/tmp/infobip_callback"; umask(0); $fd = fopen($fn,"a+"); fputs($fd,$fc); fclose($fd); die();
 */

if ($cb_timestamp && $cb_from && $cb_text) {
	$cb_datetime = date($datetime_format, $cb_timestamp);
	$sms_datetime = trim($cb_datetime);
	$sms_sender = trim($cb_from);
	$message = trim(htmlspecialchars_decode(urldecode($cb_text)));
	$sms_receiver = trim($cb_to);

	_log("sender:" . $sms_sender . " receiver:" . $sms_receiver . " dt:" . $sms_datetime . " msg:[" . $message . "]", 3, "infobip incoming");

	// collected:
	// $sms_datetime, $sms_sender, $message, $sms_receiver
	$sms_sender = addslashes($sms_sender);
	$message = addslashes($message);
	recvsms_process($sms_datetime, $sms_sender, $message, $sms_receiver, $cb_smsc);
}

if ($cb_status && $cb_apimsgid) {
	$db_query = "
		SELECT " . _DB_PREF_ . "_tblSMSOutgoing.smslog_id AS smslog_id," . _DB_PREF_ . "_tblSMSOutgoing.uid AS uid
		FROM " . _DB_PREF_ . "_tblSMSOutgoing," . _DB_PREF_ . "_gatewayInfobip_apidata
		WHERE
			" . _DB_PREF_ . "_tblSMSOutgoing.smslog_id=" . _DB_PREF_ . "_gatewayInfobip_apidata.smslog_id AND
			" . _DB_PREF_ . "_gatewayInfobip_apidata.apimsgid=?";
	$db_result = dba_query($db_query, [$cb_apimsgid]);
	$db_row = dba_fetch_array($db_result);
	$uid = $db_row['uid'];
	$smslog_id = $db_row['smslog_id'];
	if ($uid && $smslog_id) {
		$c_sms_status = 0;
		switch ($cb_status) {
			case "001":
			case "002":
			case "011":
				$c_sms_status = 0;
				break;
			// pending

			case "003":
			case "008":
				$c_sms_status = 1;
				break;
			// sent

			case "005":
			case "006":
			case "007":
			case "009":
			case "010":
			case "012":
				$c_sms_status = 2;
				break;
			// failed

			case "004":
				$c_sms_status = 3;
				break;
			// delivered
		}
		$c_sms_credit = ceil($cb_charge);

		// pending
		$p_status = 0;
		if ($c_sms_status) {
			$p_status = $c_sms_status;
		}
		dlr_update($smslog_id, $uid, $p_status);
	}
}
