<?php
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
function twilio_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "twilio_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	if ($plugin_config['twilio']['module_sender']) {
		$sms_sender = $plugin_config['twilio']['module_sender'];
	}
	
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$ok = false;
	
	logger_print("sendsms start", 3, "twilio_hook_sendsms");
	
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	
	if ($sms_sender && $sms_to && $sms_msg) {
		$url = $plugin_config['twilio']['url'] . '/2010-04-01/Accounts/' . $plugin_config['twilio']['account_sid'] . '/SMS/Messages.json';
		$data = array(
			'To' => $sms_to,
			'From' => $sms_sender,
			'Body' => $sms_msg 
		);
		if (trim($plugin_config['twilio']['callback_url'])) {
			$data['StatusCallback'] = trim($plugin_config['twilio']['callback_url']);
		}
		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_USERPWD, $plugin_config['twilio']['account_sid'] . ':' . $plugin_config['twilio']['auth_token']);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$returns = curl_exec($ch);
			curl_close($ch);
			logger_print("url:" . $url . " callback:" . $plugin_config['twilio']['callback_url'], 3, "twilio outgoing");
			$resp = json_decode($returns);
			if ($resp->status) {
				$c_status = $resp->status;
				$c_message_id = $resp->sid;
				$c_error_text = $c_status . '|' . $resp->code . '|' . $resp->message;
				logger_print("sent smslog_id:" . $smslog_id . " message_id:" . $c_message_id . " status:" . $c_status . " error:" . $c_error_text, 2, "twilio outgoing");
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_gatewayTwilio (local_smslog_id,remote_smslog_id,status,error_text)
					VALUES ('$smslog_id','$c_message_id','$c_status','$c_error_text')";
				$id = @dba_insert_id($db_query);
				if ($id && ($c_status == 'queued')) {
					$ok = true;
					$p_status = 0;
				} else {
					$p_status = 2;
				}
				dlr($smslog_id, $uid, $p_status);
			} else {
				// even when the response is not what we expected we still print it out for debug purposes
				$resp = str_replace("\n", " ", $resp);
				$resp = str_replace("\r", " ", $resp);
				logger_print("failed smslog_id:" . $smslog_id . " resp:" . $resp, 2, "twilio outgoing");
			}
		} else {
			logger_print("fail to sendsms due to missing PHP curl functions", 3, "twilio_hook_sendsms");
		}
	}
	if (!$ok) {
		$p_status = 2;
		dlr($smslog_id, $uid, $p_status);
	}
	
	logger_print("sendsms end", 3, "twilio_hook_sendsms");
	
	return $ok;
}
