<?php
function uplink_hook_playsmsd() {
    // force to check p_status=1 (sent) as getsmsstatus only check for p_status=0 (pending)
    //$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status=0 OR p_status=1";
    $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status='1' AND p_gateway='uplink'";
    $db_result = dba_query($db_query);
    while ($db_row = dba_fetch_array($db_result)) {
	$uid = $db_row['uid'];
	$smslog_id = $db_row['smslog_id'];
	$p_datetime = $db_row['p_datetime'];
	$p_update = $db_row['p_update'];
	$gpid = $db_row['p_gpid'];
	x_hook('uplink','getsmsstatus',array($gpid,$uid,$smslog_id,$p_datetime,$p_update));
    }
}

// hook_sendsms 
// called by main sms sender
// return true for success delivery
// $mobile_sender	: sender mobile number
// $sms_sender		: sender sms footer or sms sender ID
// $sms_to		: destination sms number
// $sms_msg		: sms message tobe delivered
// $gpid		: group phonebook id (optional)
// $uid			: sender User ID
// $smslog_id		: sms ID
function uplink_hook_sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
    // global $uplink_param;   // global all variables needed, eg: varibles from config.php
    // ...
    // ...
    // return true or false
    // return $ok;
    global $uplink_param;
    global $gateway_number;
    $ok = false;
    if ($uplink_param['global_sender']) {
	$sms_from = $uplink_param['global_sender'];
    } else if ($gateway_number) {
	$sms_from = $gateway_number;
    } else {
	$sms_from = $mobile_sender;
    }
    if ($sms_sender) {
	$sms_msg = $sms_msg.$sms_sender;
    }
    $sms_type = 2; // text
    if ($msg_type=="flash") {
	$sms_type = 1; // flash
    }
    if ($sms_to && $sms_msg) {

	if ($unicode) {
	    // fixme anton - this isn't right, encoding should be done in master, not locally
	    /*
	    if (function_exists('mb_convert_encoding')) {
		$sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
	    }
	    */
	    $unicode = 1;
	}
	// fixme anton - from playSMS v0.9.6 references to input.php replaced with index.php?app=webservices
	// I should add autodetect, if its below v0.9.6 should use input.php
	$query_string = "index.php?app=webservices&u=".$uplink_param['username']."&p=".$uplink_param['password']."&ta=pv&to=".urlencode($sms_to)."&from=".urlencode($sms_from)."&type=$sms_type&msg=".urlencode($sms_msg)."&unicode=".$unicode;
	$url = $uplink_param['master']."/".$query_string;

	if ($additional_param = $uplink_param['additional_param']) {
	    $additional_param = "&".$additional_param;
	}
	$url .= $additional_param;
	$url = str_replace("&&", "&", $url);

	logger_print($url, 3, "uplink outgoing");
	$fd = @implode ('', file ($url));
	if ($fd) {
	    $response = split (" ", $fd);
	    if ($response[0] == "OK") {
		$remote_slid = $response[1];
		if ($remote_slid) {
		    $db_query = "
			INSERT INTO "._DB_PREF_."_gatewayUplink (up_local_slid,up_remote_slid,up_status)
			VALUES ('$smslog_id','$remote_slid','0')
		    ";
		    $up_id = @dba_insert_id($db_query);
		    if ($up_id) {
			$ok = true;
		    }
		}
	    }
	    logger_print("smslog_id:".$smslog_id." response:".$response[0]." ".$response[1], 3, "uplink outgoing");
	} else {
	    // even when the response is not what we expected we still print it out for debug purposes
	    $fd = str_replace("\n", " ", $fd);
	    $fd = str_replace("\r", " ", $fd);
	    logger_print("smslog_id:".$smslog_id." response:".$fd, 3, "clickatell outgoing");
	}
    }
    if (!$ok) {
	$p_status = 2;
    	setsmsdeliverystatus($smslog_id,$uid,$p_status);
    }
    return $ok;
}

// hook_getsmsstatus
// called by index.php?app=menu&inc=daemon (periodic daemon) to set sms status
// no returns needed
// $p_datetime	: first sms delivery datetime
// $p_update	: last status update datetime
function uplink_hook_getsmsstatus($gpid=0,$uid="",$smslog_id="",$p_datetime="",$p_update="") {
    // global $uplink_param;
    // p_status :
    // 0 = pending
    // 1 = delivered
    // 2 = failed
    // setsmsdeliverystatus($smslog_id,$uid,$p_status);
    global $uplink_param;
    $db_query = "SELECT * FROM "._DB_PREF_."_gatewayUplink WHERE up_local_slid='$smslog_id'";
    $db_result = dba_query($db_query);
    while ($db_row = dba_fetch_array($db_result)) {
	$local_slid = $db_row['up_local_slid'];
	$remote_slid = $db_row['up_remote_slid'];
	// fixme anton - from playSMS v0.9.6 references to input.php replaced with index.php?app=webservices
	// I should add autodetect, if its below v0.9.6 should use input.php
	$query_string = "index.php?app=webservices&u=".$uplink_param['username']."&p=".$uplink_param['password']."&ta=ds&slid=".$remote_slid;
	$url = $uplink_param['master']."/".$query_string;
	$response = @implode ('', file ($url));
	switch ($response) {
	    case "1":
		$p_status = 1;
    		setsmsdeliverystatus($local_slid,$uid,$p_status);
		$db_query1 = "UPDATE "._DB_PREF_."_gatewayUplink SET c_timestamp='".mktime()."',up_status='1' WHERE up_remote_slid='$remote_slid'";
		$db_result1 = dba_query($db_query1);
		break;
	    case "3":
		$p_status = 3;
    		setsmsdeliverystatus($local_slid,$uid,$p_status);
		$db_query1 = "UPDATE "._DB_PREF_."_gatewayUplink SET c_timestamp='".mktime()."',up_status='3' WHERE up_remote_slid='$remote_slid'";
		$db_result1 = dba_query($db_query1);
		break;
	    case "2":
	    case "ERR 400":
		$p_status = 2;
    		setsmsdeliverystatus($local_slid,$uid,$p_status);
		$db_query1 = "UPDATE "._DB_PREF_."_gatewayUplink SET c_timestamp='".mktime()."',up_status='2' WHERE up_remote_slid='$remote_slid'";
		$db_result1 = dba_query($db_query1);
		break;
	}
    }
}

// hook_getsmsinbox
// called by incoming sms processor
// no returns needed
function uplink_hook_getsmsinbox() {
    // global $uplink_param;
    // $sms_datetime	: incoming sms datetime
    // $message		: incoming sms message
    // setsmsincomingaction($sms_datetime,$sms_sender,$message)
    // you must retrieve all informations needed by setsmsincomingaction()
    // from incoming sms, have a look uplink gateway module
    global $uplink_param;
    $handle = @opendir($uplink_param['path']);
    while ($sms_in_file = @readdir($handle)) {
	if (eregi("^ERR.in",$sms_in_file) && !eregi("^[.]",$sms_in_file)) {
	    $fn = $uplink_param['path']."/$sms_in_file";
	    logger_print("infile:".$fn, 3, "uplink incoming");
	    $tobe_deleted = $fn;
	    $lines = @file ($fn);
	    $sms_datetime = trim($lines[0]);
	    $sms_sender = trim($lines[1]);
	    $message = "";
	    for ($lc=2;$lc<count($lines);$lc++) {
		$message .= trim($lines[$lc]);
	    }
	    // collected:
	    // $sms_datetime, $sms_sender, $message
	    setsmsincomingaction($sms_datetime,$sms_sender,$message);
	    logger_print("sender:".$sms_sender." dt:".$sms_datetime." msg:".$message, 3, "uplink incoming");
	    @unlink($tobe_deleted);
	}
    }
}

?>