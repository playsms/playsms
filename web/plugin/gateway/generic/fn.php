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
function generic_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = 0, $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0)
{
	global $plugin_config;

	// override $plugin_config by $plugin_config from selected SMSC
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);

	// re-filter, sanitize, modify some vars if needed
	$sms_sender = isset($plugin_config['generic']['module_sender']) ? core_sanitize_sender($plugin_config['generic']['module_sender']) : core_sanitize_sender($sms_sender);
	$sms_to = core_sanitize_mobile($sms_to);
	$sms_footer = core_sanitize_footer($sms_footer);
	$sms_msg = stripslashes($sms_msg) . ($sms_footer ? ' ' . $sms_footer : '');

	if ($plugin_config['generic']['module_sender']) {
		$sms_sender = $plugin_config['generic']['module_sender'];
	}

	// log it
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " from:" . $sms_sender . " to:" . $sms_to, 3, "generic_hook_sendsms");

	$ok = false;

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

		$url = htmlspecialchars_decode($plugin_config['generic']['url']);
		$url = str_replace('{GENERIC_API_USERNAME}', urlencode($plugin_config['generic']['api_username']), $url);
		$url = str_replace('{GENERIC_API_PASSWORD}', urlencode($plugin_config['generic']['api_password']), $url);
		$url = str_replace('{GENERIC_SENDER}', urlencode($sms_sender), $url);
		$url = str_replace('{GENERIC_TO}', urlencode($sms_to), $url);
		$url = str_replace('{GENERIC_MESSAGE}', urlencode($sms_msg), $url);
		$url = str_replace('{GENERIC_CALLBACK_URL}', urlencode($plugin_config['generic']['callback_url']), $url);

		// shortcuts
		$url = str_replace('{USERNAME}', urlencode($plugin_config['generic']['api_username']), $url);
		$url = str_replace('{PASSWORD}', urlencode($plugin_config['generic']['api_password']), $url);
		$url = str_replace('{SENDER}', urlencode($sms_sender), $url);
		$url = str_replace('{TO}', urlencode($sms_to), $url);
		$url = str_replace('{MESSAGE}', urlencode($sms_msg), $url);
		$url = str_replace('{CALLBACK_URL}', urlencode($plugin_config['generic']['callback_url']), $url);

		_log("send url:[" . $url . "]", 3, "generic_hook_sendsms");

		// send it
		$response = core_get_contents($url);

		// 14395227002806904200 SENT
		// 0 User Not Found
		$resp = explode(' ', $response, 2);

		// a single non-zero respond will be considered as a SENT response
		if (isset($resp[0]) && (int) $resp[0]) {
			$remote_id = (int) $resp[0];
			_log("sent smslog_id:" . $smslog_id . " remote_id:" . $remote_id . " smsc:" . $smsc, 2, "generic_hook_sendsms");
			if (dba_update(_DB_PREF_ . '_playsms_tblSMSOutgoing', ['remote_id' => $remote_id], ['smslog_id' => $smslog_id, 'flag_deleted' => 0])) {
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
