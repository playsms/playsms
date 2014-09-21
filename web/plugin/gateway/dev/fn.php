<?php
defined('_SECURE_') or die('Forbidden');

function dev_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	$ok = false;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "dev_hook_outgoing");
	
	if ($plugin_config['dev']['enable_outgoing']) {
		$p_status = 3;
		dlr($smslog_id, $uid, $p_status);
		$ok = true;
	}
	return $ok;
}
