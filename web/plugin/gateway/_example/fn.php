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

/**
 * This function hooks sendsms() and called by daemon sendsmsd
 * 
 * @param string $smsc Selected SMSC
 * @param string $sms_sender SMS sender ID
 * @param string $sms_footer SMS message footer
 * @param string $sms_to Mobile phone number
 * @param string $sms_msg SMS message
 * @param int $uid User ID
 * @param int $gpid Group phonebook ID
 * @param int $smslog_id SMS Log ID
 * @param string $sms_type Type of SMS
 * @param int $unicode Indicate that the SMS message is in unicode
 * @return bool true if delivery successful
 */
function example_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = 0, $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0)
{
	global $plugin_config;

	// override $plugin_config by $plugin_config from selected SMSC
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);

	// re-filter, sanitize, modify some vars if needed
	$module_sender = isset($plugin_config['example']['module_sender']) && core_sanitize_sender($plugin_config['example']['module_sender'])
		? core_sanitize_sender($plugin_config['example']['module_sender']) : '';
	$sms_sender = $module_sender ?: core_sanitize_sender($sms_sender);
	$sms_to = core_sanitize_mobile($sms_to);
	$sms_footer = core_sanitize_footer($sms_footer);
	$sms_msg = stripslashes($sms_msg . $sms_footer);

	// log it
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " from:" . $sms_sender . " to:" . $sms_to, 3, "example_hook_sendsms");

	// prepare API_URL or some other way
	$api_url = $plugin_config['example']['api_url'];
	$api_url = str_replace('{API_ACCOUNT_ID}', $plugin_config['example']['api_account_id'], $api_url);
	$api_url = str_replace('{API_TOKEN}', $plugin_config['example']['api_token'], $api_url);
	$api_url = str_replace('{SENDER_ID}', $sms_sender, $api_url);

	// routine to submit data to API_URL here
	// ...
	// $response = core_get_contents($api_url);
	// ...
	// read the returns, decide to flag this delivery right away or wait for a callback
	// ...
	// example response format: [OK] [REMOTE_ID]
	/*
	if ($response = core_get_contents($api_url)) {
		$response = preg_split('/\s+/', $response);
		if ($response !== false) {
			$status = isset($response[0]) && strtoupper($response) = 'OK' ? true : false;
			$remote_id = isset($response[1]) && (int) $response ? (int) $response : 0;
			if ($status && $remote_id) {
				// save remote_id in smslog_id record, essentially mapped remote_id with local smslog_id
				if (dba_update(_DB_PREF_ . '_playsms_tblSMSOutgoing', ['remote_id' => $remote_id], ['smslog_id' => $smslog_id, 'flag_deleted' => 0])) {
					// p_status :
					// 	 0 = pending
					//   1 = sent
					//   2 = failed
					//   3 = delivered

					// some gateway update the status via callback
					// $p_status = 0;
					// other gateway will not, so just set it as 'sent'
					$p_status = 1;
					// or if we really don't care we can just assume that submitted SMS is delivered
					// $p_status = 3;
					dlr($smslog_id, $uid, $p_status);

					return true;
				}
			}
		}
	}
	*/
	// ...
	// 
	// return true or false

	// by default its a failed submission
	//$p_status = 2;
	//dlr($smslog_id, $uid, $p_status);

	return false; // or true, depends on the submission returns
}

/**
 * This function hooks getsmsstatus() and called by daemon dlrssmsd()
 * 
 * There are 2 ways getting DLRs from SMS provider or SMS gateway software
 *   1. Hooks getsmsstatus() - playSMS periodically fetchs DLRs
 *   2. Use callback URL - playSMS waits for callback call (via HTTP)
 * 
 * @param int $gpid Group phonebook ID
 * @param int $uid User ID
 * @param int $smslog_id SMS Log ID
 * @param string $p_datetime SMS delivery datetime
 * @param string $p_update SMS last update datetime
 * @return void
 */
function example_hook_getsmsstatus($gpid = 0, $uid = 0, $smslog_id = 0, $p_datetime = '', $p_update = '')
{
	//global $plugin_config;

	// use this to set delivery report for this SMS:
	// p_status :
	// 	 0 = pending
	//   1 = sent
	//   2 = failed
	//   3 = delivered
	//dlr($smslog_id, $uid, $p_status);

	return;
}

/**
 * This function hooks getsmsinbox() and called by daemon recvsmsd()
 * 
 * There are 2 ways getting incoming SMS from SMS provider or SMS gateway software
 *   1. Hooks getsmsinbox() - playSMS periodically fetchs incoming SMS
 *   2. Use callback URL - playSMS waits for callback call (via HTTP)
 * 
 * @return void
 */
function example_hook_getsmsinbox()
{
	//global $plugin_config;

	// routine for reading incoming SMS here
	// ...
	// get $sms_datetime - Incoming SMS datetime
	// get $message - Incoming SMS message
	// get $sms_sender - Sender number
	// get $sms_receiver - Receiver number
	// get $smsc - Enter SMSC name or empty it to let playSMS handles it
	// ...
	// use this to save incoming SMS:
	//recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc)

	return;
}
