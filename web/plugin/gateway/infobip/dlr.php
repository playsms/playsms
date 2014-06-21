<?php
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

$remote_addr = $_SERVER['REMOTE_ADDR'];

// srosa 20100531: added var below
$remote_host = $_SERVER['HTTP_HOST'];

// srosa 20100531: changed test below to allow hostname in bearerbox_host instead of ip
// if ($remote_addr != $kannel_param['bearerbox_host'])
// if ($remote_addr != $kannel_param['bearerbox_host'] && $remote_host != $kannel_param['bearerbox_host']) {
// logger_print("exit remote_addr:".$remote_addr." remote_host:".$remote_host." bearerbox_host:".$kannel_param['bearerbox_host'], 2, "kannel dlr");
// exit();
// }

$xml = file_get_contents('php://input');

// file_put_contents('toto.txt', $xml, true);
logger_print("dlr request: " . $xml, 3, "infobip dlr");

preg_match_all('/id=\"([0-9]+)\"/', $xml, $result);
$apimsgid = $result[1][0];
logger_print("apimsgid: " . $apimsgid, 3, "infobip dlr");

if (preg_match_all('/status=\"([A-Z]+)\"/', $xml, $result)) {
	$status = $result[1][0];
}

$db_query = "SELECT smslog_id FROM " . _DB_PREF_ . "_gatewayInfobip_apidata WHERE apimsgid='$apimsgid'";
$db_result = dba_query($db_query);
$db_row = dba_fetch_array($db_result);
$smslog_id = $db_row['smslog_id'];

$db_query = "SELECT uid FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE smslog_id='$smslog_id'";
$db_result = dba_query($db_query);
$db_row = dba_fetch_array($db_result);
$uid = $db_row['uid'];

logger_print("addr:" . $remote_addr . " host:" . $remote_host . " type:" . $status . " smslog_id:" . $smslog_id . " uid:" . $uid, 2, "infobip dlr");

if ($status && $smslog_id && $uid) {
	switch ($status) {
		case "DELIVERED":
			$p_status = 3;
			break;
			
			// delivered
			
			
		case "NOT_DELIVERED":
			$p_status = 2;
			break;
			
			// failed
			
			
		case "NOT_ENOUGH_CREDITS":
			$p_status = 2;
			break;
			
			// failed
			
			
	}
	
	dlr($smslog_id, $uid, $p_status);
	
	// log dlr
	// $db_query = "SELECT apimsgid FROM "._DB_PREF_."_gatewayInfobip_apidata WHERE smslog_id='$smslog_id'";
	// $db_result = dba_num_rows($db_query);
	// if ($db_result > 0) {
	$db_query = "UPDATE " . _DB_PREF_ . "_gatewayInfobip_apidata SET c_timestamp='" . mktime() . "', status='$status' WHERE smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
	
	// } else {
	// $db_query = "INSERT INTO "._DB_PREF_."_gatewayKannel_dlr (smslog_id,kannel_dlr_type) VALUES ('$smslog_id','$type')";
	// $db_result = dba_query($db_query);
	// }
	
	
}
