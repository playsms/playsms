<?php
defined('_SECURE_') or die('Forbidden');

// hook_sendsms
// called by main sms sender
// return true for success delivery
// $smsc		: smsc
// $sms_sender	: sender mobile number
// $sms_footer	: sender sms footer or sms sender ID
// $sms_to		: destination sms number
// $sms_msg		: sms message tobe delivered
// $uid			: sender User ID
// $gpid		: group phonebook id (optional)
// $smslog_id	: sms ID
// $sms_type	: send flash message when the value is "flash"
// $unicode		: send unicode character (16 bit)
function template_hook_sendsms($smsc, $sms_sender,$sms_footer,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
	// global $tmpl_param;   // global all variables needed, eg: varibles from config.php

	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "template_hook_sendsms");
	
	//$sms_sender = stripslashes($sms_sender);
	//$sms_footer = stripslashes($sms_footer);
	//$sms_msg = stripslashes($sms_msg);
	// ...
	// ...
	// return true or false
	// return $ok;
}

// hook_playsmsd
// used by index.php?app=main&inc=daemon to execute custom commands
//function template_hook_playsmsd() {
	// fetch every 60 seconds
	//if (!core_playsmsd_timer(60)) {
	//	return;
	//}

	// custom commands
//}

// hook_getsmsstatus
// called by index.php?app=main&inc=daemon (periodic daemon) to set sms status
// no returns needed
// $p_datetime	: first sms delivery datetime
// $p_update	: last status update datetime
function template_hook_getsmsstatus($gpid=0,$uid="",$smslog_id="",$p_datetime="",$p_update="") {
	// global $tmpl_param;
	// p_status :
	// 0 = pending
	// 1 = sent
	// 2 = failed
	// 3 = delivered
	// dlr($smslog_id,$uid,$p_status);
}

// hook_getsmsinbox
// called by incoming sms processor
// no returns needed
function template_hook_getsmsinbox() {
	// global $tmpl_param;
	// $sms_datetime	: incoming sms datetime
	// $message		: incoming sms message
	// if $sms_sender and $message are not coming from $_REQUEST then you need to addslashes it
	// $sms_sender = addslashes($sms_sender);
	// $message = addslashes($message);
	// recvsms($sms_datetime,$sms_sender,$message,$sms_receiver,'template')
	// you must retrieve all informations needed by recvsms()
	// from incoming sms, have a look gnokii gateway module
}

?>
