<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
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
	// reply message
	$reply = 'Thank you for your message';
	// detect reply message, set unicode if not ASCII
	$unicode = 0;
	if (function_exists('mb_detect_encoding')) {
		$encoding = mb_detect_encoding($reply, 'auto');
		if ($encoding != 'ASCII') {
			$unicode = 1;
		}
	}
	// send reply with admin account
	$c_uid = username2uid('admin');
	// send reply
	sendsms($core_config['main']['cfg_gateway_number'],'',$sms_sender,$reply,$c_uid,0,'text',$unicode);
	// log it
	$sms_datetime = core_display_datetime($sms_datetime);
	logger_print("dt:".$sms_datetime." s:".$sms_sender." r:".$sms_receiver." autorespon:".$reply,3,"myauto");
}

?>