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
function nexmo_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "nexmo_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	if ($plugin_config['nexmo']['module_sender']) {
		$sms_sender = $plugin_config['nexmo']['module_sender'];
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
				// $sms_msg = mb_convert_encoding($sms_msg, "UCS-2", "auto");
				$sms_msg = mb_convert_encoding($sms_msg, "UTF-8", "auto");
				$unicode_query_string = "&type=unicode"; // added at the of query string if unicode
			}
		}
		
		$query_string = "api_key=" . $plugin_config['nexmo']['api_key'] . "&api_secret=" . $plugin_config['nexmo']['api_secret'] . "&to=" . urlencode($sms_to) . "&from=" . urlencode($sms_sender) . "&text=" . urlencode($sms_msg) . $unicode_query_string . "&status-report-req=1&client-ref=" . $smslog_id;
		$url = $plugin_config['nexmo']['url'] . "?" . $query_string;
		
		_log("url:[" . $url . "]", 3, "nexmo outgoing");
		
		// fixme anton
		// rate limit to 1 second per submit - nexmo rule
		//sleep(1);
		

		// old way
		// $resp = json_decode(file_get_contents($url), true);
		

		// new way
		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header' => "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($query_string) . "\r\nConnection: close\r\n",
				'content' => $query_string 
			) 
		);
		$context = stream_context_create($opts);
		$resp = json_decode(file_get_contents($plugin_config['nexmo']['url'], FALSE, $context), TRUE);
		
		if ($resp['message-count']) {
			$c_status = $resp['messages'][0]['status'];
			$c_message_id = $resp['messages'][0]['message-id'];
			$c_network = $resp['messages'][0]['network'];
			$c_error_text = $resp['messages'][0]['error-text'];
			_log("sent smslog_id:" . $smslog_id . " message_id:" . $c_message_id . " status:" . $c_status . " error:" . $c_error_text, 2, "nexmo outgoing");
			$db_query = "
				INSERT INTO " . _DB_PREF_ . "_gatewayNexmo (local_smslog_id,remote_smslog_id,status,network,error_text)
				VALUES ('$smslog_id','$c_message_id','$c_status','$c_network','$c_error_text')";
			$id = @dba_insert_id($db_query);
			if ($id && ($c_status == 0)) {
				$ok = true;
				$p_status = 1;
				dlr($smslog_id, $uid, $p_status);
			}
		} else {
			// even when the response is not what we expected we still print it out for debug purposes
			$resp = str_replace("\n", " ", $resp);
			$resp = str_replace("\r", " ", $resp);
			_log("failed smslog_id:" . $smslog_id . " resp:" . $resp, 2, "nexmo outgoing");
		}
	}
	if (!$ok) {
		$p_status = 2;
		dlr($smslog_id, $uid, $p_status);
	}
	return $ok;
}
