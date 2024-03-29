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
function twilio_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0)
{
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

	_log("sendsms start", 3, "twilio_hook_sendsms");

	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}

	if ($sms_sender && $sms_to && $sms_msg) {
		$url = $plugin_config['twilio']['url'] . '/2010-04-01/Accounts/' . $plugin_config['twilio']['account_sid'] . '/Messages.json';
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
			_log("sendsms url:[" . $url . "] callback:[" . $plugin_config['twilio']['callback_url'] . "] smsc:[" . $smsc . "]", 3, "twilio_hook_sendsms");
			$resp = json_decode($returns);
			if ($resp->status) {
				$c_status = $resp->status;
				$c_message_id = $resp->sid;
				$c_error_text = $c_status . '|' . $resp->code . '|' . $resp->message;
				_log("sent smslog_id:" . $smslog_id . " message_id:" . $c_message_id . " status:" . $c_status . " error:" . $c_error_text . " smsc:[" . $smsc . "]", 2, "twilio_hook_sendsms");
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_gatewayTwilio (local_smslog_id,remote_smslog_id,status,error_text)
					VALUES (?,?,?,?)";
				$db_argv = [
					$smslog_id,
					$c_message_id,
					$c_status,
					$c_error_text
				];
				if (dba_insert_id($db_query, $db_argv) && ($c_status == 'queued')) {
					$ok = true;
					$p_status = 0;
				} else {
					$p_status = 2;
				}
				dlr($smslog_id, $uid, $p_status);
			} else {
				// even when the response is not what we expected we still print it out for debug purposes
				$resp = str_replace("\n", " ", core_sanitize_inputs($resp));
				$resp = str_replace("\r", " ", $resp);
				_log("failed smslog_id:" . $smslog_id . " resp:" . $resp . " smsc:[" . $smsc . "]", 2, "twilio_hook_sendsms");
			}
		} else {
			_log("fail to sendsms due to missing PHP curl functions", 3, "twilio_hook_sendsms");
		}
	}
	if (!$ok) {
		$p_status = 2;
		dlr($smslog_id, $uid, $p_status);
	}

	_log("sendsms end", 3, "twilio_hook_sendsms");

	return $ok;
}

function twilio_hook_call($requests)
{
	// please note that we must globalize these 2 variables
	global $core_config, $plugin_config;
	$called_from_hook_call = true;
	$access = $requests['access'];

	if ($access == 'callback') {
		$fn = $core_config['apps_path']['plug'] . '/gateway/twilio/callback.php';
		_log("start load:" . $fn, 2, "twilio call");
		include $fn;
		_log("end load callback", 2, "twilio call");
	}
}
