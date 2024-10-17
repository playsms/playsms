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
 * Implementations of hook keyword_isavail()
 *
 * @param string $keyword Keyword
 * @param string $sms_receiver Receiver number
 * @return boolean true if keyword is available
 */
function sms_custom_hook_keyword_isavail($keyword, $sms_receiver)
{
	$found = false;

	$keyword = strtoupper(core_sanitize_alphanumeric($keyword));
	$sms_receiver = trim($sms_receiver);

	// check with receiver number empty, a catchall
	$db_query = "SELECT custom_id FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword=? OR custom_keyword LIKE ? OR custom_keyword LIKE ? OR custom_keyword LIKE ?) AND sms_receiver=''";
	$db_argv = [$keyword, "$keyword %", "% $keyword", "% $keyword %"];
	if (dba_num_rows($db_query, $db_argv)) {
		$found = true;
	}

	// if no cathall
	if (!$found) {
		if ($sms_receiver) {
			$db_query = "SELECT custom_id FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword=? OR custom_keyword LIKE ? OR custom_keyword LIKE ? OR custom_keyword LIKE ?) AND sms_receiver=?";
			$db_argv = [$keyword, "$keyword %", "% $keyword", "% $keyword %", $sms_receiver];
		} else {
			$db_query = "SELECT custom_id FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword=? OR custom_keyword LIKE ? OR custom_keyword LIKE ? OR custom_keyword LIKE ?)";
			$db_argv = [$keyword, "$keyword %", "% $keyword", "% $keyword %"];
		}
		if (dba_num_rows($db_query, $db_argv)) {
			$found = true;
		}
	}

	// found means not available
	$ret = !$found;

	return $ret;
}

/**
 * Implementations of hook recvsms_process()
 *
 * @param string $sms_datetime date and time when incoming sms inserted to playsms
 * @param string $sms_sender sender on incoming sms
 * @param string $custom_keyword check if keyword is for sms_custom
 * @param string $custom_param get parameters from incoming sms
 * @param string $sms_receiver receiver number that is receiving incoming sms
 * @param string $smsc SMSC ID
 * @param string $raw_message raw incoming message
 * @return array $ret array of keyword owner uid and status, true if incoming sms handled
 */
function sms_custom_hook_recvsms_process($sms_datetime, $sms_sender, $custom_keyword, $custom_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	$ret = [];

	$uid = 0;
	$status = false;

	$custom_keyword = strtoupper(core_sanitize_alphanumeric($custom_keyword));
	$custom_param = trim($custom_param);

	$keyword = $custom_keyword;
	$sms_receiver = trim($sms_receiver);

	$list = [];

	// match keyword with receiver number
	if ($sms_receiver) {
		$db_query = "SELECT uid, custom_id FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword=? OR custom_keyword LIKE ? OR custom_keyword LIKE ? OR custom_keyword LIKE ?) AND sms_receiver=?";
		$db_argv = [$keyword, "$keyword %", "% $keyword", "% $keyword %", $sms_receiver];
		$db_result = dba_query($db_query, $db_argv);
		if ($db_row = dba_fetch_array($db_result)) {
			$uid = $db_row['uid'];
			$custom_id = $db_row['custom_id'];
			$list = $db_row;
		}
	}

	// look for matching with catchall, if found it will override above matches
	$db_query = "SELECT uid, custom_id FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword=? OR custom_keyword LIKE ? OR custom_keyword LIKE ? OR custom_keyword LIKE ?) AND sms_receiver=''";
	$db_argv = [$keyword, "$keyword %", "% $keyword", "% $keyword %"];
	$db_result = dba_query($db_query, $db_argv);
	if ($db_row = dba_fetch_array($db_result)) {
		$uid = $db_row['uid'];
		$custom_id = $db_row['custom_id'];
		$list = $db_row;
	}

	if ($uid && $custom_id && $list) {
		if (sms_custom_handle($list, $uid, $custom_id, $sms_datetime, $sms_sender, $sms_receiver, $keyword, $custom_param, $smsc, $raw_message)) {
			$status = true;
		}
	}

	$ret['uid'] = $uid;
	$ret['status'] = $status;

	return $ret;
}

function sms_custom_handle($list, $uid, $custom_id, $sms_datetime, $sms_sender, $sms_receiver, $custom_keyword, $custom_param = '', $smsc = '', $raw_message = '')
{
	$smsc = gateway_decide_smsc($smsc, $list['smsc']);

	$username = user_uid2username($uid);
	$keyword = $custom_keyword;

	$db_query = "SELECT custom_url,service_name,custom_return_as_reply FROM " . _DB_PREF_ . "_featureCustom WHERE custom_id=?";
	$db_result = dba_query($db_query, [$custom_id]);
	$db_row = dba_fetch_array($db_result);
	$service_name = htmlspecialchars_decode($db_row['service_name']);
	$custom_url = htmlspecialchars_decode($db_row['custom_url']);
	$custom_return_as_reply = (int) $db_row['custom_return_as_reply'];
	if (!($username && $keyword && $custom_url)) {

		return false;
	}

	$sms_datetime = core_display_datetime($sms_datetime);
	$custom_url = str_replace("{SERVICENAME}", urlencode($service_name), $custom_url);
	$custom_url = str_replace("{SMSDATETIME}", urlencode($sms_datetime), $custom_url);
	$custom_url = str_replace("{SMSSENDER}", urlencode($sms_sender), $custom_url);
	$custom_url = str_replace("{SMSRECEIVER}", urlencode($sms_receiver), $custom_url);
	$custom_url = str_replace("{CUSTOMKEYWORD}", urlencode($keyword), $custom_url);
	$custom_url = str_replace("{CUSTOMPARAM}", urlencode($custom_param), $custom_url);
	$custom_url = str_replace("{CUSTOMRAW}", urlencode($raw_message), $custom_url);
	_log("custom_url:[" . $custom_url . "]", 3, "sms_custom_handle");

	if ($returns = core_get_contents($custom_url)) {
		$unicode = core_detect_unicode($returns);
		$returns = addslashes($returns);
		_log("returns:[" . $returns . "]", 3, "sms_custom_handle");
		if ($custom_return_as_reply === 1) {
			sendsms_helper($username, $sms_sender, $returns, 'text', $unicode, $smsc);
		}
	} else {
		_log("returns empty", 3, "sms_custom_handle");
	}

	return true;
}

/**
 * Check for valid ID
 *
 * @param int $id
 * @return bool
 */
function sms_custom_check_id($id)
{
	return core_check_id($id, _DB_PREF_ . '_featureCustom', 'custom_id');
}