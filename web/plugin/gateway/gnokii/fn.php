<?php

function gnokii_hook_getsmsstatus($gpid=0,$uid="",$smslog_id="",$p_datetime="",$p_update="") {
    global $gnokii_param;
    // p_status :
    // 0 = pending
    // 1 = delivered
    // 2 = failed
    if ($gpid) {
        $fn = $gnokii_param['path']."/out.$gpid.$uid.$smslog_id";
        $efn = $gnokii_param['path']."/ERR.out.$gpid.$uid.$smslog_id";
    } else {
        $fn = $gnokii_param['path']."/out.0.$uid.$smslog_id";
        $efn = $gnokii_param['path']."/ERR.out.0.$uid.$smslog_id";
    }
    // set delivered first
    $p_status = 1;
    setsmsdeliverystatus($smslog_id,$uid,$p_status);
    // and then check if its not delivered
    if (file_exists($fn)) {
        $p_datetime_stamp = strtotime($p_datetime);
        $p_update_stamp = strtotime($p_update);
        $p_delay = floor(($p_update_stamp - $p_datetime_stamp)/86400);
	// set pending if its under 2 days
        if ($p_delay <= 2) {
    	    $p_status = 0;
    	    setsmsdeliverystatus($smslog_id,$uid,$p_status);
    	} else {
    	    $p_status = 2;
    	    setsmsdeliverystatus($smslog_id,$uid,$p_status);
    	    @unlink ($fn);
    	    @unlink ($efn);
        }
	return;
    }
    // set if its failed
    if (file_exists($efn)) {
        $p_status = 2;
        setsmsdeliverystatus($smslog_id,$uid,$p_status);
        @unlink ($fn);
    	@unlink ($efn);
	return;
    }
    return;
}

function gnokii_hook_playsmsd() {
    // nothing
}

function gnokii_hook_getsmsinbox() {
    global $gnokii_param;
    $handle = @opendir($gnokii_param['path']);
    while ($sms_in_file = @readdir($handle)) {
	if (eregi("^ERR.in",$sms_in_file) && !eregi("^[.]",$sms_in_file)) {
	    $fn = $gnokii_param['path']."/$sms_in_file";
	    logger_print("infile:".$fn, 3, "gnokii incoming");
	    $tobe_deleted = $fn;
	    $lines = @file ($fn);
	    $sms_datetime = trim($lines[0]);
	    $sms_sender = trim($lines[1]);
	    $message = "";
	    for ($lc=2;$lc<count($lines);$lc++) {
		$message .= trim($lines['$lc']);
	    }
	    // collected:
	    // $sms_datetime, $sms_sender, $message
	    setsmsincomingaction($sms_datetime,$sms_sender,$message);
	    logger_print("sender:".$sms_sender." dt:".$sms_datetime." msg:".$message, 3, "gnokii incoming");
	    @unlink($tobe_deleted);
	}
    }
}

function gnokii_hook_sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
    global $gnokii_param;
    $sms_id = "$gpid.$uid.$smslog_id";
    if (empty($sms_id)) {
	$sms_id = mktime();
    }
    if ($sms_sender) {
	$sms_msg = $sms_msg.$sms_sender;
    }
    $the_msg = "$sms_to\n$sms_msg";
    $fn = $gnokii_param['path']."/out.$sms_id";
    logger_print("outfile:".$fn, 3, "gnokii outgoing");
    umask(0);
    $fd = @fopen($fn, "w+");
    @fputs($fd, $the_msg);
    @fclose($fd);
    $ok = false;
    if (file_exists($fn)) {
	$ok = true;
    }
    return $ok;
}

?>