<?php
function kannel_hook_playsmsd() {
    // nothing
}

function kannel_hook_sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid='',$gp_code='PV',$smslog_id=0,$sms_type='text',$unicode=0) {
    global $kannel_param;
    global $gateway_number;
    global $http_path;
    $ok = false;
    if ($kannel_param['global_sender']) {
	$sms_from = $kannel_param['global_sender'];
    } else if ($gateway_number) {
	$sms_from = $gateway_number;
    } else {
	$sms_from = $mobile_sender;
    }
    if ($sms_sender) {
	$sms_msg = $sms_msg.$sms_sender;
    }
    // set failed first
    $p_status = 2;
    setsmsdeliverystatus($smslog_id,$uid,$p_status);

    $msg_type = 1; // text, default
    if ($sms_type=="flash") {
	$msg_type = 0; //flash
    }
    
    // $dlr_url = $http_path['base'] . "/plugin/gateway/kannel/dlr.php?type=%d&slid=$smslog_id&uid=$uid";
    $dlr_url = $kannel_param['playsms_web'] . "/plugin/gateway/kannel/dlr.php?type=%d&slid=$smslog_id&uid=$uid";

    $URL = "/cgi-bin/sendsms?username=".urlencode($kannel_param['username'])."&password=".urlencode($kannel_param['password']);
    $URL .= "&from=".urlencode($sms_from)."&to=".urlencode($sms_to)."&text=".urlencode($sms_msg);
    $URL .= "&dlr-mask=31&dlr-url=".urlencode($dlr_url);
    $URL .= "&mclass=".$msg_type;
    logger_print("http://".$kannel_param['bearerbox_host'].":".$kannel_param['sendsms_port'].$URL, 3, "kannel outgoing");

    // srosa 20100531: Due to improper http response from Kannel, file_get_contents cannot be used.
    // One issue is that Kannel responds with HTTP 202 whereas file_get_contents expect HTTP 200
    // The other is that a missing CRLF at the end of Kannel's message forces file_get_contents to wait forever.
    // reverting to previous way of doing things which works fine.
    /*
    if ($rv = trim(file_get_contents("$URL"))) {
	// old kannel responsed with Sent.
	// new kannel with the other 2
	if (($rv == "Sent.") || ($rv == "0: Accepted for delivery") || ($rv == "3: Queued for later delivery")) {
	    $ok = true;
	    // set pending
	    $p_status = 0;
	    setsmsdeliverystatus($smslog_id, $uid, $p_status);
	}
    }
    */
    $connection = fsockopen($kannel_param['bearerbox_host'],$kannel_param['sendsms_port'],&$error_number,&$error_description,60);
    if ($connection) {
	socket_set_blocking($connection, false);
	fputs($connection, "GET ".$URL." HTTP/1.0\r\n\r\n");
	while (!feof($connection)) {
	    $rv = fgets($connection, 128);
	    logger_print("smslog_id:".$smslog_id." response:".$rv, 3, "kannel outgoing");
	    if (($rv == "Sent.") || ($rv == "0: Accepted for delivery") || ($rv == "3: Queued for later delivery")) {
		$ok = true;
		// set pending
		$p_status = 0;
		setsmsdeliverystatus($smslog_id,$uid,$p_status);
	    }
	}
	fclose ($connection);
    }
    return $ok;
}

function kannel_hook_getsmsstatus($gp_code="",$uid="",$smslog_id="",$p_datetime="",$p_update="") {
    global $kannel_param;
    // not used, depend on kannel delivery status updater
}

function kannel_hook_getsmsinbox() {
    global $kannel_param;
    // not used, playSMS will only received HTTP pushed values from kannel
    /*
    $handle = @opendir($kannel_param['path']);
    while ($sms_in_file = @readdir($handle))
    {
	if (eregi("^ERR.in",$sms_in_file) && !eregi("^[.]",$sms_in_file))
	{
	    $fn = $kannel_param['path']."/$sms_in_file";
	    $tobe_deleted = $fn;
	    $lines = @file ($fn);
            $sms_datetime = urldecode(trim($lines[0]));
            $sms_sender = urldecode(trim($lines[1]));
            $message = "";
            for ($lc=2;$lc<count($lines);$lc++)
            {
                $message .= trim($lines[$lc]);
            }
	    // collected:
	    // $sms_datetime, $sms_sender, $message
	    setsmsincomingaction($sms_datetime,$sms_sender,$message);
	    @unlink($tobe_deleted);
	}
    }
    */
}

?>