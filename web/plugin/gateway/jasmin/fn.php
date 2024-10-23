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
function jasmin_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = 0, $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0)
{
	global $plugin_config;

	// override $plugin_config by $plugin_config from selected SMSC
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);

	// re-filter, sanitize, modify some vars if needed
	$module_sender = isset($plugin_config['jasmin']['module_sender']) && core_sanitize_sender($plugin_config['jasmin']['module_sender'])
		? core_sanitize_sender($plugin_config['jasmin']['module_sender']) : '';
	$sms_sender = $module_sender ?: core_sanitize_sender($sms_sender);
	$sms_to = core_sanitize_mobile($sms_to);
	$sms_footer = core_sanitize_footer($sms_footer);
	$sms_msg = stripslashes($sms_msg . $sms_footer);

	// log it
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " from:" . $sms_sender . " to:" . $sms_to, 3, "jasmin_hook_sendsms");

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

		$callback_url = isset($plugin_config['jasmin']['callback_authcode']) && $plugin_config['jasmin']['callback_authcode']
			? $plugin_config['jasmin']['callback_url'] . "?authcode=" . $plugin_config['jasmin']['callback_authcode'] : $plugin_config['jasmin']['callback_url'];

		$query_string = "username=" . urlencode($plugin_config['jasmin']['api_username']) . "&password=" . urlencode($plugin_config['jasmin']['api_password']);
		$query_string .= "&to=" . urlencode($sms_to) . "&from=" . urlencode($sms_sender) . "&content=" . urlencode($sms_msg) . $unicode_query_string;
		$query_string .= "&dlr=yes&dlr-level=2&dlr-method=POST&dlr-url=" . urlencode($callback_url);
		$url = $plugin_config['jasmin']['url'] . "?" . $query_string;

		_log("send url:[" . $url . "]", 3, "jasmin_hook_sendsms");

		// send it
		$response = core_get_contents($url);

		// Success "07033084-5cfd-4812-90a4-e4d24ffb6e3d"
		// Error "No route found"
		$resp = explode(' ', $response, 2);

		if (isset($resp[0]) && strtolower($resp[0]) == 'success') {
			$remote_id = isset($resp[1]) && $resp[1] ? $resp[1] : '';
			$remote_id = str_replace('"', '', $remote_id);
			if ($remote_id) {
				_log("sent smslog_id:" . $smslog_id . " remote_id:" . $remote_id . " smsc:" . $smsc, 2, "jasmin_hook_sendsms");

				if (dba_update(_DB_PREF_ . '_playsms_tblSMSOutgoing', ['remote_id' => $remote_id], ['smslog_id' => $smslog_id, 'flag_deleted' => 0])) {
					$p_status = 1;
					dlr($smslog_id, $uid, $p_status);

					return true;
				}
			}
		} else if (isset($resp[0]) && strtolower($resp[0]) == 'error' && isset($resp[1])) {
			$response = str_replace('"', '', $resp[1]);
		}

		_log("failed smslog_id:" . $smslog_id . " response:[" . $response . "] smsc:" . $smsc, 2, "jasmin_hook_sendsms");
	}

	$p_status = 2;
	dlr($smslog_id, $uid, $p_status);

	return false;
}
