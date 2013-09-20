<?php
function messagemedia_hook_getsmsstatus($gpid=0,$uid="",$smslog_id="",$p_datetime="",$p_update="") {
    global $messagemedia_param;
    list($c_sms_credit,$c_sms_status) = messagemedia_getsmsstatus($smslog_id);
    // pending
    $p_status = 0;
    if ($c_sms_status) {
	$p_status = $c_sms_status;
    }
    setsmsdeliverystatus($smslog_id,$uid,$p_status);
}

/*
function messagemedia_hook_playsmsd() {
    // force to check p_status=1 (sent) as getsmsstatus only check for p_status=0 (pending)
    //$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status=0 OR p_status=1";
    $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status='1' AND p_gateway='messagemedia'";
    $db_result = dba_query($db_query);
    while ($db_row = dba_fetch_array($db_result)) {
	$uid = $db_row['uid'];
	$smslog_id = $db_row['smslog_id'];
	$p_datetime = $db_row['p_datetime'];
	$p_update = $db_row['p_update'];
	$gpid = $db_row['p_gpid'];
	x_hook('messagemedia','getsmsstatus',array($gpid,$uid,$smslog_id,$p_datetime,$p_update));
    }
}
*/

function messagemedia_hook_sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
    global $messagemedia_param;
    global $gateway_number;

	// Message media
	require_once("SmsInterface.inc");

	//logger_print("Messagemedia: Message sent to".$sms_to);
	logger_print($messagemedia_param['username']);
	logger_print($messagemedia_param['password']);
	
	$si = new SmsInterface (false, false);
	/*
	$si->addMessage (
		$phone, // String
		$messageText, // String
		$messageID, // Integer, optional, default = 0
		$delay, // Integer, optional, default = 0
		$validityPeriod, // Integer, optional, default = 169
		$deliveryReport // Boolean, optional, default = false
	);
	*/
	$si->addMessage ($sms_to, $sms_msg, $smslog_id, 0, 169, true);

	$ok = false;
    // failed
    $p_status = 2;
    setsmsdeliverystatus($smslog_id,$uid,$p_status);
    // API ID = SMS log ID - No return ID by Message Media, ID is provided by PlaySMS
    $apimsgid = $smslog_id;
    messagemedia_setsmsapimsgid($smslog_id,$apimsgid);
	
	if (!$si->connect ($messagemedia_param['username'], $messagemedia_param['password'], true, false))
	    //echo "failed. Could not contact server.\n";
	    logger_print("Messagemedia: failed. Could not contact server.");
	elseif (!$si->sendMessages ()) {
	    //echo "failed. Could not send message to server.\n";
	    logger_print("Messagemedia: failed. Could not send message to server.");
	    if ($si->getResponseMessage () !== NULL)
		//echo "<BR>Reason: " . $si->getResponseMessage () . "\n";
		logger_print("Messagemedia: Reason: " . $si->getResponseMessage () );
	} else {
	    //echo "OK.\n";
	    logger_print("Messagemedia: OK. sent");
		// sent
		$p_status = 1;
		$ok = true;
	}
	logger_print("smslog_id:".$smslog_id." charge:".$c_sms_credit." sms_status:".$p_status." response:".$response[0]." ".$response[1], 3, "messagemedia outgoing");
	setsmsdeliverystatus($smslog_id,$uid,$p_status);
    return $ok;
}

function messagemedia_hook_getsmsinbox() {
    // fixme anton - messagemedia will only receive incoming sms from callback url
    /*
    global $messagemedia_param;
    $handle = @opendir($messagemedia_param['incoming_path']);
    while ($sms_in_file = @readdir($handle)) {
	if (eregi("^ERR.in",$sms_in_file) && !eregi("^[.]",$sms_in_file)) {
	    $fn = $messagemedia_param['incoming_path']."/$sms_in_file";
	    $tobe_deleted = $fn;
	    $lines = @file ($fn);
	    $sms_datetime = trim($lines[0]);
	    $sms_sender = trim($lines[1]);
	    $message = "";
	    for ($lc=2;$lc<count($lines);$lc++) {
		$message .= trim($lines[$lc]);
	    }
	    // collected:
	    // $sms_datetime, $sms_sender, $message, $sms_receiver
	    setsmsincomingaction($sms_datetime,$sms_sender,$message,$sms_receiver);
	    @unlink($tobe_deleted);
	}
    }
    */
}

function messagemedia_getsmsstatus($smslog_id) {
    global $messagemedia_param;
    $c_sms_status = 0;
    $c_sms_credit = 0;
    $db_query = "SELECT apimsgid FROM "._DB_PREF_."_gatewayMessagemedia_apidata WHERE smslog_id='$smslog_id'";
    $db_result = dba_query($db_query);
    $db_row = dba_fetch_array($db_result);
    if ($apimsgid = $db_row['apimsgid']) {
	$query_string = "getmsgcharge?api_id=".$messagemedia_param['api_id']."&user=".$messagemedia_param['username']."&password=".$messagemedia_param['password']."&apimsgid=$apimsgid";
	$url = $messagemedia_param['send_url']."/".$query_string;
	logger_print("smslog_id:".$smslog_id." apimsgid:".$apimsgid." url:".$url, 3, "messagemedia getsmsstatus");
	$fd = @implode ('', file ($url));
	if ($fd) {
    	    $response = split (" ", $fd);
    	    $err_code = trim ($response[1]);
	    $credit = 0;
    	    if ((strtoupper(trim($response[2])) == "CHARGE:")) {
		$credit = intval(trim($response[3]));
	    }
	    $c_sms_credit = $credit;
	    if ((strtoupper(trim($response[4])) == "STATUS:")) {
		$status = trim($response[5]);
		switch ($status) {
		    case "001":
		    case "002":
		    case "011": $c_sms_status = 0; break; // pending
		    case "003":
		    case "008": $c_sms_status = 1; break; // sent
		    case "005":
		    case "006":
		    case "007":
		    case "009":
		    case "010":
		    case "012": $c_sms_status = 2; break; // failed
		    case "004": $c_sms_status = 3; break; // delivered
		}
	    }
	    logger_print("smslog_id:".$smslog_id." apimsgid:".$apimsgid." charge:".$credit." status:".$status." sms_status:".$c_sms_status, 3, "messagemedia getsmsstatus");
	}
    }
    return array ($c_sms_credit, $c_sms_status);
}

function messagemedia_setsmsapimsgid($smslog_id,$apimsgid) {
    if ($smslog_id && $apimsgid) {
	$db_query = "INSERT INTO "._DB_PREF_."_gatewayMessagemedia_apidata (smslog_id,apimsgid) VALUES ('$smslog_id','$apimsgid')";
	$db_result = dba_query($db_query);
    }
}

function messagemedia_hook_call($requests) {
    global $apps_path, $http_path, $core_config, $messagemedia_param;
    $called_from_hook_call = true;
    $access = $requests['access'];
    if ($access == 'callback') {
	$fn = $apps_path['plug'].'/gateway/messagemedia/callback.php';
	logger_print("start load:".$fn, 3, "messagemedia call");
	include $fn;
	logger_print("end load callback", 3, "messagemedia call");
    }
}

?>
