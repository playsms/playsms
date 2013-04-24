<?php
defined('_SECURE_') or die('Forbidden');

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
	$unicode = core_detect_unicode($reply);
	// send reply
	list($ok, $to, $smslog_id, $queue) = sendsms('admin', $sms_sender, $reply, 'text', $unicode);
	// log it
	$sms_datetime = core_display_datetime($sms_datetime);
	logger_print("dt:".$sms_datetime." s:".$sms_sender." r:".$sms_receiver." autorespon:".$reply, 2, "myauto");
}

?>