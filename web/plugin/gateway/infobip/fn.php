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
function infobip_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = 0, $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0)
{
	global $plugin_config;

	// override $plugin_config by $plugin_config from selected SMSC
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);

	// re-filter, sanitize, modify some vars if needed
	$sms_sender = isset($plugin_config['infobip']['module_sender']) ? core_sanitize_sender($plugin_config['infobip']['module_sender']) : core_sanitize_sender($sms_sender);
	$sms_to = core_sanitize_mobile($sms_to);
	$sms_footer = core_sanitize_footer($sms_footer);
	$sms_msg = stripslashes($sms_msg) . ($sms_footer ? ' ' . $sms_footer : '');

	// log it
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " from:" . $sms_sender . " to:" . $sms_to, 3, "infobip_hook_sendsms");

	$smsType = "&SMSText";
	switch ($sms_type) {
		case "flash":
			$sms_type = 1;
			break;
		case "text":
		default:
			$sms_type = 0;
	}

	if ($unicode) {
		if (function_exists('mb_convert_encoding')) {
			$sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
		}
		$sms_msg = core_str2hex($sms_msg);
		$unicode = 8;
		$smsType = "&binary";
	}

	// query_string = "sendmsg?api_id=".$plugin_config['infobip']['api_id']."&user=".$plugin_config['infobip']['username']."&password=".$plugin_config['infobip']['password']."&to=".urlencode($sms_to)."&msg_type=$sms_type&text=".urlencode($sms_msg)."&unicode=".$unicode.$set_sms_from;
	$query_string = "sendsms/plain?user=" . $plugin_config['infobip']['username'] . "&password=" . $plugin_config['infobip']['password'];
	$query_string .= "&GSM=" . urlencode($sms_to) . $smsType . "=" . urlencode($sms_msg) . "&sender=" . $sms_sender;
	$query_string .= "&IsFlash=" . $sms_type . "&DataCoding=" . $unicode;

	$url = $plugin_config['infobip']['send_url'] . "/" . $query_string;

	$dlr_nopush = $plugin_config['infobip']['dlr_nopush'];
	if ($dlr_nopush == '0') {
		$additional_param = "&nopush=0";
	} elseif ($dlr_nopush == '1') {
		$additional_param = "&nopush=1";
	}

	if ($additional_param = $plugin_config['infobip']['additional_param']) {
		$additional_param .= "&" . $additional_param;
	}

	$url .= $additional_param;
	$url = str_replace("&&", "&", $url);

	_log("url:[" . $url . ']', 3, "infobip outgoing");

	$xml = core_get_contents($url);

	if ($xml && $response = core_xml_to_array($xml)) {
		$result_status = isset($response['result']['status']) ? trim($response['result']['status']) : '';
		if ($result_status === "0") {
			$p_status = 1; // sent
			_log("smslog_id:" . $smslog_id . " p_status:" . $p_status, 2, "infobip outgoing");
		} elseif ($result_status === "-2") {
			$p_status = 2;
			_log("smslog_id:" . $smslog_id . " response:" . $result_status . " NOT_ENOUGH_CREDIT", 2, "infobip outgoing");
		} else {
			$p_status = 2;
			// even when the response is not what we expected we still print it out for debug purposes
			_log("smslog_id:" . $smslog_id . " response:" . $result_status . " UNKNOWN_CODE", 2, "infobip outgoing");
		}

		dlr($smslog_id, $uid, $p_status);

		return true;
	} else {
		_log("no response smslog_id:" . $smslog_id, 3, "infobip outgoing");
	}
	if (!$ok) {
		$p_status = 2;
	}
	dlr($smslog_id, $uid, $p_status);
	return $ok;
}
