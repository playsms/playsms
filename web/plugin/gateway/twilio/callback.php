<?php
error_reporting(0);

if (!$called_from_hook_call) {
	chdir("../../../");
	
	// ignore CSRF
	$core_config['init']['ignore_csrf'] = TRUE;
	
	include "init.php";
	include $core_config['apps_path']['libs'] . "/function.php";
	chdir("plugin/gateway/twilio/");
	$requests = $_REQUEST;
}

$log = '';
if (is_array($requests)) {
	foreach ($requests as $key => $val ) {
		$log .= $key . ':' . $val . ' ';
	}
	logger_print("pushed " . $log, 2, "twilio callback");
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
			case "sent" :
				$p_status = 1;
				break; // delivered
			default :
				$p_status = 2;
				break; // failed
		}
		logger_print("dlr uid:" . $uid . " smslog_id:" . $smslog_id . " message_id:" . $remote_smslog_id . " status:" . $status, 2, "twilio callback");
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
	logger_print("incoming smsc:" . $smsc . " message_id:" . $remote_smslog_id . " s:" . $sms_sender . " d:" . $sms_receiver, 2, "twilio callback");
	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
}

