<?php
defined('_SECURE_') or die('Forbidden');

/*
 * intercept incoming sms and look for @ sign followed by username
 * this feature will replace:
 *   @username <private message>
 * to:
 *   PV username <private message>
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
function pvat_hook_interceptincomingsms($sms_datetime, $sms_sender, $message, $sms_receiver) {
	$msg = explode(' ', $message);
	$ret = array();
	if (count($msg) > 1) {
		$in['sms_datetime'] = $sms_datetime;
		$in['sms_sender'] = $sms_sender;
		$in['message'] = $message;
		$in['sms_receiver'] = $sms_receiver;
		$in['msg'] = $msg;
		$ret = pvat_handle($in);
	}
	return $ret;
}

function pvat_handle($in) {
	$ret = array();
	$in['sms_datetime'] = core_display_datetime($in['sms_datetime']);
	for ($i=0;$i<count($in['msg']);$i++) {
		$c_text = trim($in['msg'][$i]);
		if (substr($c_text, 0, 1) == '@') {
			$c_username = substr($c_text, 1);
			$c_username = str_replace(',', '', $c_username);
			$c_username = str_replace(':', '', $c_username);
			$c_username = str_replace(';', '', $c_username);
			$c_username = str_replace('!', '', $c_username);
			$c_username = str_replace('?', '', $c_username);
			$c_username = str_replace("'", '', $c_username);
			$c_username = str_replace('"', '', $c_username);
			if ($c_uid = username2uid($c_username)) {
				insertsmstoinbox($in['sms_datetime'], $in['sms_sender'], $c_username, $in['message'], $in['sms_receiver']);
				logger_print("inbox:".$c_username." uid:".$c_uid." dt:".$in['sms_datetime']." s:".$in['sms_sender']." r:".$in['sms_receiver']." m:".$in['message'], 3, "pvat");
				$ret['hooked'] = true;
			}
		}
	}
	return $ret;
}

?>