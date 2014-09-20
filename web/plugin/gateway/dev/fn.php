<?php
defined('_SECURE_') or die('Forbidden');

function dev_hook_sendsms($vgw, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	$ok = false;
	if ($plugin_config['dev']['enable_outgoing']) {
		_log('vgw:' . $vgw . ' s:' . $sms_sender . ' to:' . $sms_to, 3, 'dev_hook_sendsms');
		$p_status = 3;
		dlr($smslog_id, $uid, $p_status);
		$ok = true;
	}
	return $ok;
}
