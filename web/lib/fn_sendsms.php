<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

function sendsms_getvalidnumber($sender) {
    $sender_arr = explode(" ", $sender);
    $sender = preg_replace("/[^a-zA-Z0-9\+]/", "", $sender_arr[0]);
    if (strlen($sender) > 20) {
	$sender = substr($sender, 0, 20);
    }
    return $sender;
}

function sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid,$gpid=0,$sms_type='text',$unicode=0) {
    global $datetime_now, $core_config, $gateway_module;
    $ok = false;
    $username = uid2username($uid);
    
    // make sure sms_datetime is in supported format and in GMT+0
    // timezone used for outgoing message is not module timezone, but gateway timezone
    // module gateway may have set already to +0000 (such kannel and clickatell)
    $sms_datetime = core_adjust_datetime($core_config['datetime']['now'], $core_config['main']['datetime_timezone']);
    
    // fixme anton - mobile number can be anything, screened by gateway
    // $mobile_sender = sendsms_getvalidnumber($mobile_sender);
    
    $sms_to = sendsms_getvalidnumber($sms_to);
    logger_print("start", 3, "sendsms");
    if (rate_cansend($username, $sms_to)) {
	// fixme anton - its a total mess ! need another DBA
	$sms_sender = addslashes($sms_sender);
	$sms_msg = addslashes($sms_msg);
	// we save all info first and then process with gateway module
	// the thing about this is that message saved may not be the same since gateway may not be able to process
	// message with that length or certain characters in the message are not supported by the gateway
	$db_query = "
    	    INSERT INTO "._DB_PREF_."_tblSMSOutgoing 
    	    (uid,p_gpid,p_gateway,p_src,p_dst,p_footer,p_msg,p_datetime,p_sms_type,unicode) 
    	    VALUES ('$uid','$gpid','$gateway_module','$mobile_sender','$sms_to','$sms_sender','$sms_msg','$sms_datetime','$sms_type','$unicode')
	";
	logger_print("saving:$uid,$gpid,$gateway_module,$mobile_sender,$sms_to,$sms_type,$unicode", 3, "sendsms");
	// continue to gateway only when save to db is true
	if ($smslog_id = @dba_insert_id($db_query)) {
	    logger_print("smslog_id:".$smslog_id." saved", 3, "sendsms");
	    // fixme anton - another mess !
	    $sms_sender = stripslashes($sms_sender);
	    $sms_msg = stripslashes($sms_msg);
	    if (x_hook($gateway_module, 'sendsms', array($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid,$gpid,$smslog_id,$sms_type,$unicode))) {
		// fixme anton - deduct user's credit as soon as gateway returns true
		rate_deduct($smslog_id);
		$ok = true;
	    }
	}
    }
    $ret['status'] = $ok;
    $ret['smslog_id'] = $smslog_id;
    return $ret;
}

function sendsms_pv($username,$sms_to,$message,$sms_type='text',$unicode=0) {
    global $apps_path, $core_config;
    global $datetime_now, $gateway_module;
    $uid = username2uid($username);
    $mobile_sender = username2mobile($username);
    $max_length = $core_config['smsmaxlength'];
    if ($sms_sender = username2sender($username)) {
	$max_length = $max_length - strlen($sms_sender) - 1;
    }
    if (strlen($message)>$max_length) {
        $message = substr ($message,0,$max_length-1);
    }
    $sms_msg = $message;
    
    // \r and \n is ok - http://smstools3.kekekasvi.com/topic.php?id=328
    //$sms_msg = str_replace("\r","",$sms_msg);
    //$sms_msg = str_replace("\n","",$sms_msg);
    $sms_msg = str_replace("\"","'",$sms_msg);

    $mobile_sender = str_replace("\'","",$mobile_sender);
    $mobile_sender = str_replace("\"","",$mobile_sender);
    $sms_sender = str_replace("\'","",$sms_sender);
    $sms_sender = str_replace("\"","",$sms_sender);
    if (is_array($sms_to)) {
	$array_sms_to = $sms_to;
    } else {
	$array_sms_to[0] = $sms_to;
    }
    for ($i=0;$i<count($array_sms_to);$i++) {
	$c_sms_to = str_replace("\'","",$array_sms_to[$i]);
	$c_sms_to = str_replace("\"","",$c_sms_to);
	$to[$i] = $c_sms_to;
	$ok[$i] = false;
	if ($ret = sendsms($mobile_sender,$sms_sender,$c_sms_to,$sms_msg,$uid,0,$sms_type,$unicode)) {
	    $ok[$i] = $ret['status'];
	    $smslog_id[$i] = $ret['smslog_id'];
	}
    }
    return array($ok,$to,$smslog_id);
}

function sendsms_bc($username,$gpid,$message,$sms_type='text',$unicode=0) {
    global $apps_path, $core_config;
    global $datetime_now, $gateway_module;
    $uid = username2uid($username);
    $max_length = $core_config['smsmaxlength'];
    if ($sms_sender = username2sender($username)) {
	$sms_sender = str_replace("\'","",$sms_sender);
	$sms_sender = str_replace("\"","",$sms_sender);
	$max_length = $max_length - strlen($sms_sender) - 1; 
    }
    if (strlen($message)>$max_length) {
        $message = substr ($message,0,$max_length-1);
    }
    $sms_msg = $message;
    // \r and \n is ok - http://smstools3.kekekasvi.com/topic.php?id=328
    //$sms_msg = str_replace("\r","",$sms_msg);
    //$sms_msg = str_replace("\n","",$sms_msg);
    $sms_msg = str_replace("\"","'",$sms_msg);
	    
    $mobile_sender = username2mobile($username);
    $mobile_sender = str_replace("\'","",$mobile_sender);
    $mobile_sender = str_replace("\"","",$mobile_sender);
    
    // destination group should be an array, if single then make it array of 1 member
    if (is_array($gpid)) {
	$array_gpid = $gpid;
    } else {
	$array_gpid[0] = $gpid;
    }

    $j=0;
    for ($i=0;$i<count($array_gpid);$i++) {
	$c_gpid = strtoupper($array_gpid[$i]);
	$rows = phonebook_getdatabyid($c_gpid);
	foreach ($rows as $key => $db_row) {
	    $p_num = $db_row['p_num'];
	    $sms_to = $p_num;
	    $sms_to = str_replace("\'","",$sms_to);
	    $sms_to = str_replace("\"","",$sms_to);
	    $to[$j] = $sms_to;
	    $ok[$j] = 0;
	    if ($ret = sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid,$c_gpid,$sms_type,$unicode)) {
	        $ok[$j] = $ret['status'];
		$smslog_id[$i] = $ret['smslog_id'];
	    }
	    $j++;
	}
    }
    return array($ok,$to,$smslog_id);
}

?>