<?php

/*
 * intercept incoming sms and look for keyword 'xlate'
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
 * @return
 *   array $ret
 */
function xlate_hook_interceptincomingsms($sms_datetime, $sms_sender, $message) {
    $msg = explode(" ", $message);
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
		$ret['param_modified'] = 1;
		// this time only message param changed
		$ret['param']['message'] = $new_message;
		logger_print("dt:".$sms_datetime." s:".$sms_sender." m:".$message." mod:".$ret['param']['message'],3,"xlate");
	    }
	}
    }
    return $ret;
}

?>