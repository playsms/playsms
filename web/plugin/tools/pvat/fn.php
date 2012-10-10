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
	$msg = explode(" ", $message);
	$ret = array();
	if (count($msg) > 1) {
		$pv = trim($msg[0]);
		if (substr($pv,0,1) == '@') {
			$c_username = substr($pv,1);
			$new_message = "PV ".$c_username." ";
			if (username2uid($c_username)) {
				for ($i=1;$i<count($msg);$i++) {
					$new_message .= $msg[$i]." ";
				}
				$new_message = substr($new_message,0,-1);
				// set 1 to param_modified to let parent function modify param values
				$ret['modified'] = true;
				// this time only message param changed
				$ret['param']['message'] = $new_message;
				$sms_datetime = core_display_datetime($sms_datetime);
				logger_print("dt:".$sms_datetime." s:".$sms_sender." r:".$sms_receiver." m:".$message." mod:".$ret['param']['message'], 3, "pvat");
				// do not forget to tell parent that this SMS has been hooked
				$ret['hooked'] = true;
			}
		}
	}
	return $ret;
}

?>