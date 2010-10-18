<?php
function kannel_hook_playsmsd() {
    // nothing
}

function kannel_hook_sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
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
    
    // this doesn't work properly if kannel is not on the same server with playSMS
    // $dlr_url = $http_path['base'] . "/plugin/gateway/kannel/dlr.php?type=%d&slid=$smslog_id&uid=$uid";
    
    // prior to 0.9.5.1
    // $dlr_url = $kannel_param['playsms_web'] . "/plugin/gateway/kannel/dlr.php?type=%d&slid=".$smslog_id."&uid=".$uid;
    // since 0.9.5.1
    $dlr_url = $kannel_param['playsms_web'] . "/index.php?app=call&cat=gateway&plugin=kannel&access=dlr&type=%d&slid=".$smslog_id."&uid=".$uid;

    $URL = "/cgi-bin/sendsms?username=".urlencode($kannel_param['username'])."&password=".urlencode($kannel_param['password']);
    $URL .= "&from=".urlencode($sms_from)."&to=".urlencode($sms_to);
    $URL .= "&dlr-mask=31&dlr-url=".urlencode($dlr_url);
    $URL .= "&mclass=".$msg_type;
    
    if ($unicode) {
	if (function_exists('mb_convert_encoding')) {
	    $sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
	    $URL .= "&charset=UTF-16BE";
	}
	$URL .= "&coding=2";
    }

    $URL .= "&text=".urlencode($sms_msg);
    
    // fixme anton - patch 1.4.3, dlr requries smsc-id, you should add at least smsc=<your smsc-id in kannel.conf> from web
    if ($additional_param = $kannel_param['additional_param']) {
	$additional_param = "&".$additional_param;
    } else {
	$additional_param = "&smsc=default";
    }
    $URL .= $additional_param;
    $URL = str_replace("&&", "&", $URL);
    
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
    // fixme anton - deprecated when using PHP5
    //$connection = fsockopen($kannel_param['bearerbox_host'],$kannel_param['sendsms_port'],&$error_number,&$error_description,60);
    $connection = fsockopen($kannel_param['bearerbox_host'],$kannel_param['sendsms_port'],$error_number,$error_description,60);
    if ($connection) {
	socket_set_blocking($connection, false);
	fputs($connection, "GET ".$URL." HTTP/1.0\r\n\r\n");
	while (!feof($connection)) {
	    $rv = fgets($connection, 128);
	    if (($rv == "Sent.") || ($rv == "0: Accepted for delivery") || ($rv == "3: Queued for later delivery")) {
		$ok = true;
		// set pending
		$p_status = 0;
		logger_print("smslog_id:".$smslog_id." response:".$rv, 3, "kannel outgoing");
		setsmsdeliverystatus($smslog_id,$uid,$p_status);
	    }
	}
	fclose ($connection);
    }
    return $ok;
}

function kannel_hook_getsmsstatus($gpid=0,$uid="",$smslog_id="",$p_datetime="",$p_update="") {
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

function kannel_hook_call($requests) {
    global $apps_path;
    $called_from_hook_call = true;
    $access = $requests['access'];
    if ($access = 'dlr') {
	logger_print("start dlr.php", 3, "kannel call");
	include $apps_path['plug'].'/gateway/kannel/dlr.php';
	logger_print("end dlr.php", 3, "kannel call");
    }
    if ($access = 'geturl') {
	logger_print("start geturl.php", 3, "kannel call");
	include $apps_path['plug'].'/gateway/kannel/geturl.php';
	logger_print("end geturl.php", 3, "kannel call");
    }
}

?>