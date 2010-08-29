<?php

function checkavailablekeyword($keyword)
{
    global $reserved_keywords, $core_config;
    $ok = true;
    $reserved = false;
    for ($i=0;$i<count($reserved_keywords);$i++)
    {
        if ($keyword == $reserved_keywords[$i])
        {
    	    $reserved = true;
	}
    }
    if ($reserved)
    {
	$ok = false;	
    }
    else
    {
	for ($c=0;$c<count($core_config['featurelist']);$c++)
	{
	    if (x_hook($core_config['featurelist'][$c],'checkavailablekeyword',array($keyword)))
	    {
		$ok = false;
		break;
	    }
	}
    }
    return $ok;
}

function setsmsincomingaction($sms_datetime,$sms_sender,$message)
{
    global $gateway_module, $core_config;
    $c_uid = 0;
    $c_feature = "";
    $ok = false;
    $array_target_keyword = explode(" ",$message);
    $target_keyword = strtoupper(trim($array_target_keyword[0]));
    $message_full = $message;
    $message = $array_target_keyword[1];
    for ($i=2;$i<count($array_target_keyword);$i++)
    {
	$message .= " ".$array_target_keyword[$i];
    }
    switch ($target_keyword)
    {
	case "BC":
	    $c_uid = mobile2uid($sms_sender);
	    $c_feature = 'core';
	    $array_target_group = explode(" ",$message);
	    $target_group = strtoupper(trim($array_target_group[0]));
	    $message = $array_target_group[1];
	    for ($i=2;$i<count($array_target_group);$i++)
	    {
		$message .= " ".$array_target_group[$i];
	    }
	    if (send2group($sms_sender,$target_group,$message))
	    {
		$ok = true;
	    }
	    break;
	case "PV":
	    $c_feature = 'core';
	    $array_target_user = explode(" ",$message);
	    $target_user = strtoupper(trim($array_target_user[0]));
	    $c_uid = username2uid($target_user);
	    $message = $array_target_user[1];
	    for ($i=2;$i<count($array_target_user);$i++)
	    {
		$message .= " ".$array_target_user[$i];
	    }
	    if (insertsmstoinbox($sms_datetime,$sms_sender,$target_user,$message))
	    {
		$ok = true;
	    }
	    break;
	default:
	    for ($c=0;$c<count($core_config['featurelist']);$c++)
	    {
		$c_feature = $core_config['featurelist'][$c];
		$ret = x_hook($c_feature,'setsmsincomingaction',array($sms_datetime,$sms_sender,$target_keyword,$message));
		if ($ok = $ret['status'])
		{
		    $c_uid = $ret['uid'];
		    break;
		}
	    }
    }

    $c_status = ( $ok ? 1 : 0 );
    if ($c_status == 0)
    {
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

function insertsmstoinbox($sms_datetime,$sms_sender,$target_user,$message)
{
    global $web_title,$email_service,$email_footer;
    $ok = false;
    if ($sms_sender && $target_user && $message)
    {
	$db_query = "SELECT uid,email,mobile FROM "._DB_PREF_."_tblUser WHERE username='$target_user'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result))
	{
	    $uid = $db_row[uid];
	    $email = $db_row[email];
	    $mobile = $db_row[mobile];
	    $db_query = "
		INSERT INTO "._DB_PREF_."_tblUserInbox
		(in_sender,in_uid,in_msg,in_datetime) 
		VALUES ('$sms_sender','$uid','$message','$sms_datetime')
	    ";
	    if ($cek_ok = @dba_insert_id($db_query))
	    {
		if ($email)
		{
		    $subject = "[SMSGW-PV] from $sms_sender";
		    $body = "Forward Private WebSMS ($web_title)\n\n";
		    $body .= "Date Time: $sms_datetime\n";
		    $body .= "Sender: $sms_sender\n";
		    $body .= "Receiver: $mobile\n\n";
		    $body .= "Message:\n$message\n\n";
		    $body .= $email_footer."\n\n";
		    sendmail($email_service,$email,$subject,$body);
		}
		$ok = true;
	    }
	}
    }
    return $ok;
}

function setsmsdeliverystatus($smslog_id,$uid,$p_status)
{
    global $datetime_now;
    // $p_status = 0 --> pending
    // $p_status = 1 --> sent
    // $p_status = 2 --> failed
    // $p_status = 3 --> delivered
    $ok = false;
    $db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".mktime()."',p_update='$datetime_now',p_status='$p_status' WHERE smslog_id='$smslog_id' AND uid='$uid'";
    if ($aff_id = @dba_affected_rows($db_query))
    {
	$ok = true;
	// fixme anton - temporary modification and only reduce credit when $p_status=1
	if ($p_status == 1) {
	    setsmscredit($smslog_id);
	}
    }
    return $ok;
}

function setsmscredit($smslog_id) {
    $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE smslog_id='$smslog_id'";
    $db_result = dba_query($db_query);
    $db_row = dba_fetch_array($db_result);
    $p_dst = $db_row['p_dst'];
    $p_msg = $db_row['p_msg'];
    $uid = $db_row['uid'];
    $count = ceil(strlen($p_msg) / 140);
    $rate = getsmsrate($p_dst);
    $username = uid2username($uid);
    $credit = username2credit($username);
    $remaining = $credit - ($rate*$count);
    setusersmscredit($uid, $remaining);
    return;
}

function setusersmscredit($uid, $remaining) {
    $db_query = "UPDATE "._DB_PREF_."_tblUser SET c_timestamp=NOW(),credit='$remaining' WHERE uid='$uid'";
    $db_result = @dba_affected_rows($db_query);
}

function getsmsrate($p_dst) {
    $rate = 0;
    $prefix = $p_dst;
    for ($i=11;$i>0;$i--) {
	$prefix = substr($prefix, 0, $i);
	$db_query = "SELECT rate FROM "._DB_PREF_."_tblRate WHERE prefix LIKE '$prefix'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
	    $rate = $db_row['rate'];
	    break;
	}
    }
    return $rate;
}

function q_sanitize($var)
{
    $var = str_replace("/","",$var);
    $var = str_replace("|","",$var);
    $var = str_replace("\\","",$var);
    $var = str_replace("\"","",$var);
    $var = str_replace('\'',"",$var);
    $var = str_replace("..","",$var);
    return $var;
}
			    
function x_hook($c_plugin, $c_function, $c_param=array())
{
    $c_fn = $c_plugin.'_hook_'.$c_function;
    if (function_exists($c_fn))
    {
	return call_user_func_array($c_fn,$c_param);
    }
}

function getsmsinbox()
{
    global $gateway_module;
    x_hook($gateway_module,'getsmsinbox');
}

function getsmsstatus()
{
    global $gateway_module;
    $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status='0' AND p_gateway='$gateway_module'";
    $db_result = dba_query($db_query);
    while ($db_row = dba_fetch_array($db_result))
    {
	$uid = $db_row[uid];
	$smslog_id = $db_row[smslog_id];
	$p_datetime = $db_row[p_datetime];
	$p_update = $db_row[p_update];
	$gpid = $db_row[p_gpid];
	$gp_code = gpid2gpcode($gpid);
	x_hook($gateway_module,'getsmsstatus',array($gp_code,$uid,$smslog_id,$p_datetime,$p_update));
    }
}

function execcommoncustomcmd()
{
    global $apps_path;
    @include $apps_path[incs]."/common/customcmd.php";
}


function playsmsd()
{
    global $core_config, $gateway_module;
    // plugin tools
    for ($c=0;$c<count($core_config['toolslist']);$c++)
    {
	x_hook($core_config['toolslist'][$c],'playsmsd');
    }
    // plugin feature
    for ($c=0;$c<count($core_config['featurelist']);$c++)
    {
	x_hook($core_config['featurelist'][$c],'playsmsd');
    }
    // plugin gateway
    x_hook($gateway_module,'playsmsd');
}

?>
