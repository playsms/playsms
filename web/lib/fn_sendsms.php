<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

function str2hex($string)  {
    $hex = '';
    $len = strlen($string);
    for ($i = 0; $i < $len; $i++) {
	$hex .= str_pad(dechex(ord($string[$i])), 2, 0, STR_PAD_LEFT);
    }
    return $hex;
}

function sendsms_getvalidnumber($sender) {
    $sender_arr = explode(" ", $sender);
    $sender = preg_replace("/[^a-zA-Z0-9\+]/", "", $sender_arr[0]);
    if (strlen($sender) > 20) {
	$sender = substr($sender, 0, 20);
    }
    return $sender;
}

function sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid,$gpid=0,$sms_type='text',$unicode=0) {
    global $datetime_now, $gateway_module;
    $ok = false;
    $username = uid2username($uid);
    $mobile_sender = sendsms_getvalidnumber($mobile_sender);
    $sms_to = sendsms_getvalidnumber($sms_to);
    logger_print("start", 3, "sendsms");
    if (rate_cansend($username, $sms_to)) {
	$db_query = "
    	    INSERT INTO "._DB_PREF_."_tblSMSOutgoing 
    	    (uid,p_gpid,p_gateway,p_src,p_dst,p_footer,p_msg,p_datetime,p_sms_type,unicode) 
    	    VALUES ('$uid','$gpid','$gateway_module','$mobile_sender','$sms_to','$sms_sender','$sms_msg','$datetime_now','$sms_type','$unicode')
	";
	logger_print("saving:$uid,$gpid,$gateway_module,$mobile_sender,$sms_to,$sms_type,$unicode", 3, "sendsms");
	if ($smslog_id = @dba_insert_id($db_query)) {
	    logger_print("smslog_id:".$smslog_id." saved", 3, "sendsms");
	    // fixme anton - when magic_quotes_gpc disabled we need to stripslashes sms_msg and sms_sender
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

function websend2pv($username,$sms_to,$message,$sms_type='text',$unicode=0) {
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
	$c_sms_to = str_replace("\"","",$array_sms_to[$i]);
	$to[$i] = $c_sms_to;
	$ok[$i] = false;
	if ($ret = sendsms($mobile_sender,$sms_sender,$c_sms_to,$sms_msg,$uid,0,$sms_type,$unicode)) {
	    $ok[$i] = $ret['status'];
	    $smslog_id[$i] = $ret['smslog_id'];
	}
    }
    return array($ok,$to,$smslog_id);
}

function websend2group($username,$gpid,$message,$sms_type='text',$unicode=0) {
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
	    $sms_msg = $message;
	    
	    // \r and \n is ok - http://smstools3.kekekasvi.com/topic.php?id=328
	    //$sms_msg = str_replace("\r","",$sms_msg);
	    //$sms_msg = str_replace("\n","",$sms_msg);
	    $sms_msg = str_replace("\"","'",$sms_msg);
	    
	    $mobile_sender = str_replace("\'","",$mobile_sender);
	    $mobile_sender = str_replace("\"","",$mobile_sender);
	    $sms_sender = str_replace("\'","",$sms_sender);
	    $sms_sender = str_replace("\"","",$sms_sender);
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

function send2group($mobile_sender,$gpid,$message) {
    global $apps_path, $core_config;
    global $datetime_now,$gateway_module;
    $ok = false;
    if ($mobile_sender && $gpid && $message) {
	$uid = mobile2uid($mobile_sender);
	$username = uid2username($uid);
	$sms_sender = username2sender($username);
	if ($uid && $username) {
	    $c=0;
	    $rows = phonebook_getdatabyid($gpid);
	    foreach ($rows as $key => $db_row) {
	    	$p_num = $db_row['p_num'];
		$sms_to = $p_num;
		$max_length = $core_config['smsmaxlength'] - strlen($sms_sender) - 3;
		if (strlen($message)>$max_length) {
		    $message = substr ($message,0,$max_length-1);
		}
		$sms_msg = $message;
		$sms_msg = str_replace("\r","",$sms_msg);
		$sms_msg = str_replace("\n","",$sms_msg);
		$sms_msg = str_replace("\""," ",$sms_msg);
		$mobile_sender = str_replace("\'","",$mobile_sender);
		$mobile_sender = str_replace("\"","",$mobile_sender);
		$sms_sender = str_replace("\'","",$sms_sender);
		$sms_sender = str_replace("\"","",$sms_sender);
		$sms_to = str_replace("\'","",$sms_to);
		$sms_to = str_replace("\"","",$sms_to);
		if ($ret = sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid,$gpid)) {
		    $ok[$c] = $ret['status'];
		    $c++;
		}
	    }
	}
    }
    for ($c=0;$c<count($ok);$c++) {
	if ($ok[$c]) break;
    }
    return $ok[$c];
}

?>