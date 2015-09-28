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
function generic_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "generic_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	if ($plugin_config['generic']['module_sender']) {
		$sms_sender = $plugin_config['generic']['module_sender'];
	}
	
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$ok = false;
	
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	
	if ($sms_sender && $sms_to && $sms_msg) {
		
		$unicode_query_string = '';
		if ($unicode) {
			if (function_exists('mb_convert_encoding')) {
				// $sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
				$sms_msg = mb_convert_encoding($sms_msg, "UCS-2", "auto");
				// $sms_msg = mb_convert_encoding($sms_msg, "UTF-8", "auto");
				$unicode_query_string = "&coding=8"; // added at the of query string if unicode
			}
		}
		
		// $plugin_config['generic']['default_url'] = 'http://example.api.url/handler.php?user={GENERIC_API_USERNAME}&pwd={GENERIC_API_PASSWORD}&sender={GENERIC_SENDER}&msisdn={GENERIC_TO}&message={GENERIC_MESSAGE}&dlr-url={GENERIC_CALLBACK_URL}';
		$url = htmlspecialchars_decode($plugin_config['generic']['url']);
		$url = str_replace('{GENERIC_API_USERNAME}', urlencode($plugin_config['generic']['api_username']), $url);
		$url = str_replace('{GENERIC_API_PASSWORD}', urlencode($plugin_config['generic']['api_password']), $url);
		$url = str_replace('{GENERIC_SENDER}', urlencode($sms_sender), $url);
		$url = str_replace('{GENERIC_TO}', urlencode($sms_to), $url);
		$url = str_replace('{GENERIC_MESSAGE}', urlencode($sms_msg), $url);
		$url = str_replace('{GENERIC_CALLBACK_URL}', urlencode($plugin_config['generic']['callback_url']), $url);
		
		_log("send url:[" . $url . "]", 3, "generic_hook_sendsms");
		
		// send it
		$response = file_get_contents($url);
		
		// 14395227002806904200 SENT
		// 0 User Not Found
		$resp = explode(' ', $response, 2);
		
		// a single non-zero respond will be considered as a SENT response
		if ($resp[0]) {
			$c_message_id = (int) $resp[0];
			_log("sent smslog_id:" . $smslog_id . " message_id:" . $c_message_id . " smsc:" . $smsc, 2, "generic_hook_sendsms");
			$db_query = "
				INSERT INTO " . _DB_PREF_ . "_gatewayGeneric_log (local_smslog_id, remote_smslog_id)
				VALUES ('$smslog_id', '$c_message_id')";
			$id = @dba_insert_id($db_query);
			if ($id) {
				$ok = true;
				$p_status = 1;
				dlr($smslog_id, $uid, $p_status);
			} else {
				$ok = true;
				$p_status = 0;
				dlr($smslog_id, $uid, $p_status);
			}
		} else {
			// even when the response is not what we expected we still print it out for debug purposes
			if ($resp[0] === '0') {
				$resp = trim($resp[1]);
			} else {
				$resp = $response;
			}
			_log("failed smslog_id:" . $smslog_id . " resp:[" . $resp . "] smsc:" . $smsc, 2, "generic_hook_sendsms");
		}
	}
	if (!$ok) {
		$p_status = 2;
		dlr($smslog_id, $uid, $p_status);
	}
	
	return $ok;
}
