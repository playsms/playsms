<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

// hook_sendsms
// called by main sms sender
// return true for success delivery
// $smsc : smsc
// $sms_sender : sender mobile number
// $sms_footer : sender sms footer or sms sender ID
// $sms_to : destination sms number
// $sms_msg : sms message tobe delivered
// $gpid : group phonebook id (optional)
// $uid : sender User ID
// $smslog_id : sms ID
function openvox_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "openvox_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	
	if ($plugin_config['openvox']['gateway_host'] && $plugin_config['openvox']['gateway_port'] && $sms_to && $sms_msg) {
		$query_string = "username=" . $plugin_config['openvox']['username'] . "&password=" . $plugin_config['openvox']['password'] . "&phonenumber=" . urlencode($sms_to) . "&message=" . urlencode($sms_msg) . "&report=JSON&smslog_id=" . $smslog_id;
		$url = 'http://' . $plugin_config['openvox']['gateway_host'] . ":" . $plugin_config['openvox']['gateway_port'] . '/sendsms?' . $query_string;
		
		_log("url:[" . $url . "]", 3, "openvox outgoing");
		
		$resp = json_decode(file_get_contents($url), true);
		$data = $resp['report'][0][0][0];
		$data['message'] = $resp['message'];
		
		_log('response result:' . $data['result'] . ' port:' . $data['port'] . ' to:' . $data['phonenumber'] . ' time:' . $data['time'], 3, 'openvox_hook_sendsms');
		
		if ($data['result'] == 'success') {
			$p_status = 1;
			dlr($smslog_id, $uid, $p_status);
		} else {
			$p_status = 2;
			dlr($smslog_id, $uid, $p_status);
		}
	}
	
	return TRUE;
}
