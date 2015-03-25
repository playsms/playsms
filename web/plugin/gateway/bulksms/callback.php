<?php
error_reporting(0);

// inspired by http://www.bulksms.com/int/docs/eapi/status_reports/http_push/

if (!$called_from_hook_call) {
	chdir("../../../");
	
	// ignore CSRF
	$core_config['init']['ignore_csrf'] = TRUE;
	
	include "init.php";
	include $core_config['apps_path']['libs'] . "/function.php";
	chdir("plugin/gateway/bulksms/");
}

$cb_from = $_REQUEST['source_id'];
$cb_to = $_REQUEST['msisdn'];
$cb_completed_time = $_REQUEST['completed_time'];  //r check format  yy-MM-dd HH:mm:ss
$cb_status = $_REQUEST['status'];
$cb_apimsgid = $_REQUEST['batch_id'];
$cb_smsc = (trim($_REQUEST['smsc']) ? trim($_REQUEST['smsc']) : 'bulksms');

if ($cb_completed_time && $cb_from && $$cb_apimsgid) {
	$cb_datetime = date($datetime_format, strtotime($cb_completed_time));
	$sms_datetime = trim($cb_datetime);
	$sms_sender = trim($cb_from);
	$sms_receiver = trim($cb_to);
	$apimsgid = trim($cb_apimsgid);
	$message = "not given";
	
	logger_print("sender:" . $sms_sender . " receiver:" . $sms_receiver . " dt:" . $sms_datetime . " batchid:" . $apimsgid." message:".$message, 3, "bulksms incoming");
	
	// collected:
	// $sms_datetime, $sms_sender, $message, $sms_receiver
	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $cb_smsc);
}

if ($cb_status && $cb_apimsgid) {
	$db_query = "
		SELECT " . _DB_PREF_ . "_tblSMSOutgoing.smslog_id AS smslog_id," . _DB_PREF_ . "_tblSMSOutgoing.uid AS uid
		FROM " . _DB_PREF_ . "_tblSMSOutgoing," . _DB_PREF_ . "_gatewayBulksms_apidata
		WHERE
			" . _DB_PREF_ . "_tblSMSOutgoing.smslog_id=" . _DB_PREF_ . "_gatewayBulksms_apidata.smslog_id AND
			" . _DB_PREF_ . "_gatewayBulksms_apidata.apimsgid='$cb_apimsgid'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$uid = $db_row['uid'];
	$smslog_id = $db_row['smslog_id'];
	if ($uid && $smslog_id) {
		$c_sms_status = 0;

		switch ($cb_status) {
			case "0" :
				$c_sms_status = 0;
				break; // pending
			case "10" :						
			case "12" :
				$c_sms_status = 1;
				break; // sent
			case "11" :
				$c_sms_status = 3;
				break; // delivered
			default:
				$c_sms_status = 2;
		}
		// $c_sms_credit = ceil($cb_charge);
		// pending
		$p_status = 0;
		if ($c_sms_status) {
			$p_status = $c_sms_status;
		}
		dlr($smslog_id, $uid, $p_status);
	}
}
