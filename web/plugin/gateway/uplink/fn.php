<?php
function uplink_hook_playsmsd()
{
    // nothing
}

// hook_sendsms 
// called by main sms sender
// return true for success delivery
// $mobile_sender	: sender mobile number
// $sms_sender		: sender sms footer or sms sender ID
// $sms_to		: destination sms number
// $sms_msg		: sms message tobe delivered
// $gp_code		: group phonebook code (optional)
// $uid			: sender User ID
// $smslog_id		: sms ID
function uplink_hook_sendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid='',$gp_code='PV',$smslog_id=0,$sms_type='text',$unicode=0)
{
    // global $uplink_param;   // global all variables needed, eg: varibles from config.php
    // ...
    // ...
    // return true or false
    // return $ok;
    global $uplink_param;
    global $gateway_number;
    $ok = false;
    if ($gateway_number)
    {
	$sms_from = $gateway_number;
    }
    else
    {
	$sms_from = $mobile_sender;
    }
    if ($sms_sender)
    {
	$sms_msg = $sms_msg.$sms_sender;
    }
    $sms_type = 2; // text
    if ($msg_type=="flash")
    {
	$sms_type = 1; // flash
    }
    if ($sms_to && $sms_msg)
    {
	$query_string = "input.php?u=".$uplink_param['username']."&p=".$uplink_param['password']."&ta=pv&to=".urlencode($sms_to)."&from=".urlencode($sms_from)."&type=$sms_type&msg=".urlencode($sms_msg);
	$url = $uplink_param['master']."/".$query_string;
	$fd = @implode ('', file ($url));
	if ($fd)
	{
	    $response = split (" ", $fd);
	    if ($response[0] == "OK")
	    {
		$remote_slid = $response[1];
		if ($remote_slid)
		{
		    $db_query = "
			INSERT INTO "._DB_PREF_."_gatewayUplink (up_local_slid,up_remote_slid,up_status)
			VALUES ('$smslog_id','$remote_slid','0')
		    ";
		    $up_id = @dba_insert_id($db_query);
		    if ($up_id)
		    {
			$ok = true;
		    }
		}
	    }
	}
    }
    if (!$ok)
    {
	$p_status = 2;
    	setsmsdeliverystatus($smslog_id,$uid,$p_status);
    }
    return $ok;
}

// hook_getsmsstatus
// called by menu.php?inc=daemon (periodic daemon) to set sms status
// no returns needed
// $p_datetime	: first sms delivery datetime
// $p_update	: last status update datetime
function uplink_hook_getsmsstatus($gp_code="",$uid="",$smslog_id="",$p_datetime="",$p_update="")
{
    // global $uplink_param;
    // p_status :
    // 0 = pending
    // 1 = delivered
    // 2 = failed
    // setsmsdeliverystatus($smslog_id,$uid,$p_status);
    global $uplink_param;
    $db_query = "SELECT * FROM "._DB_PREF_."_gatewayUplink WHERE up_status='0' AND up_local_slid='$smslog_id'";
    $db_result = dba_query($db_query);
    while ($db_row = dba_fetch_array($db_result))
    {
	$local_slid = $db_row['up_local_slid'];
	$remote_slid = $db_row['up_remote_slid'];
	$query_string = "input.php?u=".$uplink_param['username']."&p=".$uplink_param['password']."&ta=ds&slid=".$remote_slid;
	$url = $uplink_param['master']."/".$query_string;
	$response = @implode ('', file ($url));
	switch ($response)
	{
	    case "1":
		$p_status = 1;
    		setsmsdeliverystatus($local_slid,$uid,$p_status);
		$db_query1 = "UPDATE "._DB_PREF_."_gatewayUplink SET c_timestamp='".mktime()."',up_status='1' WHERE up_remote_slid='$remote_slid'";
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
function uplink_hook_getsmsinbox()
{
    // global $uplink_param;
    // $sms_datetime	: incoming sms datetime
    // $message		: incoming sms message
    // setsmsincomingaction($sms_datetime,$sms_sender,$message)
    // you must retrieve all informations needed by setsmsincomingaction()
    // from incoming sms, have a look uplink gateway module
    global $uplink_param;
    $handle = @opendir($uplink_param['path']);
    while ($sms_in_file = @readdir($handle))
    {
	if (eregi("^ERR.in",$sms_in_file) && !eregi("^[.]",$sms_in_file))
	{
	    $fn = $uplink_param['path']."/$sms_in_file";
	    $tobe_deleted = $fn;
	    $lines = @file ($fn);
	    $sms_datetime = trim($lines[0]);
	    $sms_sender = trim($lines[1]);
	    $message = "";
	    for ($lc=2;$lc<count($lines);$lc++)
	    {
		$message .= trim($lines['$lc']);
	    }
	    // collected:
	    // $sms_datetime, $sms_sender, $message
	    setsmsincomingaction($sms_datetime,$sms_sender,$message);
	    @unlink($tobe_deleted);
	}
    }
}

?>