<?php
error_reporting(0);

if (!$called_from_hook_call) {
	chdir("../../../");
	
	// ignore CSRF
	$core_config['init']['ignore_csrf'] = TRUE;
	
	include "init.php";
	include $core_config['apps_path']['libs'] . "/function.php";
	chdir("plugin/gateway/clickatell/");
}

$cb_from = $_REQUEST['from'];
$cb_to = $_REQUEST['to'];
$cb_timestamp = $_REQUEST['timestamp'];
$cb_text = $_REQUEST['text'];
$cb_status = $_REQUEST['status'];
$cb_charge = $_REQUEST['charge'];
$cb_apimsgid = $_REQUEST['apiMsgId'];
$cb_smsc = (trim($_REQUEST['smsc']) ? trim($_REQUEST['smsc']) : 'clickatell');

if ($cb_timestamp && $cb_from && $cb_text) {
	$cb_datetime = date($datetime_format, $cb_timestamp);
	$sms_datetime = trim($cb_datetime);
	$sms_sender = trim($cb_from);
	$message = trim($cb_text);
	$sms_receiver = trim($cb_to);
	
	logger_print("sender:" . $sms_sender . " receiver:" . $sms_receiver . " dt:" . $sms_datetime . " msg:" . $message, 3, "clickatell incoming");
	
	// collected:
	// $sms_datetime, $sms_sender, $message, $sms_receiver
	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $cb_smsc);
}

if ($cb_status && $cb_apimsgid) {
	$db_query = "
		SELECT " . _DB_PREF_ . "_tblSMSOutgoing.smslog_id AS smslog_id," . _DB_PREF_ . "_tblSMSOutgoing.uid AS uid
		FROM " . _DB_PREF_ . "_tblSMSOutgoing," . _DB_PREF_ . "_gatewayClickatell_apidata
		WHERE
			" . _DB_PREF_ . "_tblSMSOutgoing.smslog_id=" . _DB_PREF_ . "_gatewayClickatell_apidata.smslog_id AND
			" . _DB_PREF_ . "_gatewayClickatell_apidata.apimsgid='$cb_apimsgid'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$uid = $db_row['uid'];
	$smslog_id = $db_row['smslog_id'];
	if ($uid && $smslog_id) {
		$c_sms_status = 0;
		switch ($cb_status) {
			case "001" :
			case "002" :
			case "011" :
				$c_sms_status = 0;
				break; // pending
			case "003" :
			case "008" :
				$c_sms_status = 1;
				break; // sent
			case "005" :
			case "006" :
			case "007" :
			case "009" :
			case "010" :
			case "012" :
				$c_sms_status = 2;
				break; // failed
			case "004" :
				$c_sms_status = 3;
				break; // delivered
		}
		$c_sms_credit = ceil($cb_charge);
		// pending
		$p_status = 0;
		if ($c_sms_status) {
			$p_status = $c_sms_status;
		}
		dlr($smslog_id, $uid, $p_status);
	}
}
