<?php
defined('_SECURE_') or die('Forbidden');

function dev_hook_getsmsinbox() {
	global $dev_param, $core_config;
	// collected:
	// $sms_datetime, $sms_sender, $message, $sms_receiver
	if ($dev_param['enable_incoming']) {
		$sms_sender = '+62876543210';
		$sms_receiver = '1234';
		$sms_datetime = $core_config['datetime']['now'];
		$message = '@admin This is a test message';
		$sms_sender = addslashes($sms_sender);
		$message = addslashes($message);
		setsmsincomingaction($sms_datetime,$sms_sender,$message,$sms_receiver);
		logger_print("sender:".$sms_sender." receiver:".$sms_receiver." dt:".$sms_datetime." msg:".$message, 3, "dev incoming");
	}
}

function dev_hook_sendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
	global $dev_param;
	$ok = false;
	if ($dev_param['enable_outgoing']) {
		$p_status = 3;
		setsmsdeliverystatus($smslog_id,$uid,$p_status);
		$ok = true;
	}
	return $ok;
}
?>