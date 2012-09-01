<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
<?php

/*
 * intercept sendsms and replace certain word templates in $sms_msg
 *
 * @param $sms_sender
 *   sender number
 * @param $sms_footer
 *   sender signiture/footer
 * @param $sms_to
 *   destination number
 * @param $sms_msg
 *   SMS message
 * @param $uid
 *   User ID
 * @param $gpid
 *   Group phonebook ID
 * @param $sms_type
 *   Type of SMS
 * @param $unicode
 *   Whether or not a unicode message
 * @return
 *   array $ret
 */
function msgtemplate_hook_interceptsendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid,$gpid,$sms_type,$unicode) {
	// parameters modified
	$ret['modified'] = true;

	// the modification to $sms_msg
	$text = $sms_msg;
	$text = str_replace('#NAME#', phonebook_number2name($sms_to), $text);
	$text = str_replace('#NUM#', $sms_to, $text);
	$ret['param']['sms_msg'] = $text;

	// log it
	logger_print("to:".$sms_to." msg:".$sms_msg." replacedby:".$ret['param']['sms_msg'], 3, "msgtemplate");

	return $ret;
}

?>