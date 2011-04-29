<?php

/*
 * intercept incoming sms and response with a text
 * this is an example plugin
 *
 * @param $sms_datetime
 *   incoming SMS date/time
 * @param $sms_sender
 *   incoming SMS sender
 * @message
 *   incoming SMS message before interepted
 * @param $sms_receiver
 *   receiver number that is receiving incoming SMS
 * @return
 *   array $ret
 */
function myauto_hook_interceptincomingsms($sms_datetime, $sms_sender, $message, $sms_receiver) {
	global $core_config;
	$message = 'Thank you for your message';
	$c_uid = username2uid('admin');
	sendsms($core_config['main']['cfg_gateway_number'],'',$sms_sender,$message,$c_uid,0,'text',$unicode);
	logger_print("dt:".$sms_datetime." s:".$sms_sender." r:".$sms_receiver." m:".$message." autorespon:".$message,3,"myauto");
}

?>