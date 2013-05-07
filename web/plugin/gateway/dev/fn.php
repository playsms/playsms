<?php
defined('_SECURE_') or die('Forbidden');

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