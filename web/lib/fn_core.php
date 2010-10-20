<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

function checkavailablekeyword($keyword) {
    global $reserved_keywords, $core_config;
    $ok = true;
    $reserved = false;
    for ($i=0;$i<count($reserved_keywords);$i++) {
        if ($keyword == $reserved_keywords[$i]) {
    	    $reserved = true;
	}
    }
    if ($reserved) {
	$ok = false;	
    } else {
	for ($c=0;$c<count($core_config['featurelist']);$c++) {
	    if (x_hook($core_config['featurelist'][$c],'checkavailablekeyword',array($keyword))) {
		$ok = false;
		break;
	    }
	}
    }
    return $ok;
}

function interceptincomingsms($sms_datetime,$sms_sender,$message) {
    global $core_config;
    $ret = array();
    for ($c=0;$c<count($core_config['toolslist']);$c++) {
	$ret = x_hook($core_config['toolslist'][$c],'interceptincomingsms',array($sms_datetime,$sms_sender,$message));
    }
    // $ret['stop'] true is given by hooking functions, so if its empty we should set it to false
    // $ret['stop'] true will stop setsmsincomingaction process at this point
    if (! $ret['stop']) { $ret['stop'] = false; };
    return $ret;
}

function setsmsincomingaction($sms_datetime,$sms_sender,$message) {
    global $gateway_module, $core_config;
    
    // incoming sms will be handled by plugin/tools/* first
    // and then plugin/feature/* only when $ret['stop'] = false
    // by default $ret['stop'] = false
    $ret = interceptincomingsms($sms_datetime,$sms_sender,$message);
    if ($ret['stop']) {
	return true;
    }
    
    $c_uid = 0;
    $c_feature = "";
    $ok = false;
    $array_target_keyword = explode(" ",$message);
    $target_keyword = strtoupper(trim($array_target_keyword[0]));
    $message_full = $message;
    $message = $array_target_keyword[1];
    for ($i=2;$i<count($array_target_keyword);$i++) {
	$message .= " ".$array_target_keyword[$i];
    }
    switch ($target_keyword) {
	case "BC":
	    $c_uid = mobile2uid($sms_sender);
	    $c_username = uid2username($c_uid);
	    $c_feature = 'core';
	    $array_target_group = explode(" ",$message);
	    $target_group = strtoupper(trim($array_target_group[0]));
	    $c_gpid = phonebook_groupcode2id($c_uid, $target_group);
	    $message = $array_target_group[1];
	    for ($i=2;$i<count($array_target_group);$i++) {
		$message .= " ".$array_target_group[$i];
	    }
	    list($ok,$to,$smslog_id) = sendsms_bc($c_username,$c_gpid,$message);
	    $ok = true;
	    break;
	case "PV":
	    $c_feature = 'core';
	    $array_target_user = explode(" ",$message);
	    $target_user = strtoupper(trim($array_target_user[0]));
	    $c_uid = username2uid($target_user);
	    $message = $array_target_user[1];
	    for ($i=2;$i<count($array_target_user);$i++) {
		$message .= " ".$array_target_user[$i];
	    }
	    if (insertsmstoinbox($sms_datetime,$sms_sender,$target_user,$message)) {
		$ok = true;
	    }
	    break;
	default:
	    for ($c=0;$c<count($core_config['featurelist']);$c++) {
		$c_feature = $core_config['featurelist'][$c];
		$ret = x_hook($c_feature,'setsmsincomingaction',array($sms_datetime,$sms_sender,$target_keyword,$message));
		if ($ok = $ret['status']) {
		    $c_uid = $ret['uid'];
		    break;
		}
	    }
    }
    $c_status = ( $ok ? 1 : 0 );
    if ($c_status == 0) {
	$c_feature = '';
	$target_keyword = '';
	$message = $message_full;
    }
    $db_query = "
        INSERT INTO "._DB_PREF_."_tblSMSIncoming 
        (in_uid,in_feature,in_gateway,in_sender,in_keyword,in_message,in_datetime,in_status)
        VALUES
        ('$c_uid','$c_feature','$gateway_module','$sms_sender','$target_keyword','$message','$sms_datetime','$c_status')
    ";
    $db_result = dba_query($db_query);
    return $ok;
}

function interceptsmstoinbox($sms_datetime,$sms_sender,$target_user,$message) {
    global $core_config;
    $ret = array();
    for ($c=0;$c<count($core_config['toolslist']);$c++) {
	$ret = x_hook($core_config['toolslist'][$c],'interceptsmstoinbox',array($sms_datetime,$sms_sender,$target_user,$message));
    }
    // $ret['stop'] true is given by hooking functions, so if its empty we should set it to false
    // $ret['stop'] true will stop insertsmstoinbox process at this point
    if (! $ret['stop']) { $ret['stop'] = false; };
    return $ret;
}

function insertsmstoinbox($sms_datetime,$sms_sender,$target_user,$message) {
    global $web_title,$email_service,$email_footer;
    
    // sms to inbox will be handled by plugin/tools/* first
    // and then normal process only when $ret['stop'] = false
    // by default $ret['stop'] = false
    $ret = interceptsmstoinbox($sms_datetime,$sms_sender,$target_user,$message);
    if ($ret['stop']) {
	return true;
    }
    
    $ok = false;
    if ($sms_sender && $target_user && $message) {
	if ($uid = username2uid($target_user)) {
	    $email = username2email($target_user);
	    $mobile = username2mobile($target_user);
	    $db_query = "
		INSERT INTO "._DB_PREF_."_tblUserInbox
		(in_sender,in_uid,in_msg,in_datetime) 
		VALUES ('$sms_sender','$uid','$message','$sms_datetime')
	    ";
	    if ($cek_ok = @dba_insert_id($db_query)) {
		if ($email) {
		    $subject = "[SMSGW-PV] "._('from')." $sms_sender";
		    $body = _('Forward Private WebSMS')." ($web_title)\n\n";
		    $body .= _('Date time').": $sms_datetime\n";
		    $body .= _('Sender').": $sms_sender\n";
		    $body .= _('Receiver').": $mobile\n\n";
		    $body .= _('Message').":\n$message\n\n";
		    $body .= $email_footer."\n\n";
		    sendmail($email_service,$email,$subject,$body);
		}
		$ok = true;
	    }
	}
    }
    return $ok;
}

function setsmsdeliverystatus($smslog_id,$uid,$p_status) {
    global $core_config, $datetime_now;
    // $p_status = 0 --> pending
    // $p_status = 1 --> sent
    // $p_status = 2 --> failed
    // $p_status = 3 --> delivered
    $ok = false;
    $db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".mktime()."',p_update='$datetime_now',p_status='$p_status' WHERE smslog_id='$smslog_id' AND uid='$uid'";
    if ($aff_id = @dba_affected_rows($db_query)) {
	$ok = true;
	if ($p_status > 0) {
	    for ($c=0;$c<count($core_config['toolslist']);$c++) {
		x_hook($core_config['toolslist'][$c],'setsmsdeliverystatus',array($smslog_id,$uid,$p_status));
	    }
	    for ($c=0;$c<count($core_config['featurelist']);$c++) {
		x_hook($core_config['featurelist'][$c],'setsmsdeliverystatus',array($smslog_id,$uid,$p_status));
	    }
	    x_hook($gateway_module,'setsmsdeliverystatus',array($smslog_id,$uid,$p_status));
	}
    }
    return $ok;
}

function q_sanitize($var) {
    $var = str_replace("/","",$var);
    $var = str_replace("|","",$var);
    $var = str_replace("\\","",$var);
    $var = str_replace("\"","",$var);
    $var = str_replace('\'',"",$var);
    $var = str_replace("..","",$var);
    return $var;
}
			    
function x_hook($c_plugin, $c_function, $c_param=array()) {
    $c_fn = $c_plugin.'_hook_'.$c_function;
    if ($c_plugin && $c_function && function_exists($c_fn)) {
	return call_user_func_array($c_fn, $c_param);
    }
}

function getsmsinbox() {
    global $gateway_module;
    x_hook($gateway_module,'getsmsinbox');
}

function getsmsstatus() {
    global $gateway_module;
    $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status='0' AND p_gateway='$gateway_module'";
    $db_result = dba_query($db_query);
    while ($db_row = dba_fetch_array($db_result)) {
	$uid = $db_row['uid'];
	$smslog_id = $db_row['smslog_id'];
	$p_datetime = $db_row['p_datetime'];
	$p_update = $db_row['p_update'];
	$gpid = $db_row['p_gpid'];
	x_hook($gateway_module,'getsmsstatus',array($gpid,$uid,$smslog_id,$p_datetime,$p_update));
    }
}

function execcommoncustomcmd() {
    global $apps_path;
    @include $apps_path['incs']."/common/customcmd.php";
}


function playsmsd() {
    global $core_config, $gateway_module;
    // plugin tools
    for ($c=0;$c<count($core_config['toolslist']);$c++) {
	x_hook($core_config['toolslist'][$c],'playsmsd');
    }
    // plugin feature
    for ($c=0;$c<count($core_config['featurelist']);$c++) {
	x_hook($core_config['featurelist'][$c],'playsmsd');
    }
    // plugin gateway
    x_hook($gateway_module,'playsmsd');
}

function str2hex($string)  {
    $hex = '';
    $len = strlen($string);
    for ($i = 0; $i < $len; $i++) {
	$hex .= str_pad(dechex(ord($string[$i])), 2, 0, STR_PAD_LEFT);
    }
    return $hex;
}

/*
 * Format text for safe display on the web
 * @param $text
 *    original text
 * @param $len
 *    max. length of word in $text, split if more than $len
 * @return
 *    formatted text
 */
function core_display_text($text, $len=0) {
    $text = htmlspecialchars($text);
    if ($len) {
	$arr = explode(" ",$text);
	for ($i=0;$i<count($arr);$i++) {
	    if (strlen($arr[$i]) > $len) {
		$arr2 = str_split($arr[$i], $len);
		$arr[$i] = '';
		for ($j=0;$j<count($arr2);$j++) {
		    $arr[$i] .= $arr2[$j]."\n";
		}
	    }
	}
	$text = implode(" ",$arr);
    }
    return $text;
}
?>