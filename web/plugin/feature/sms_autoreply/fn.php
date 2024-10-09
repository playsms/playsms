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
 * @param string $keyword SMS keyword
 * @return bool true if keyword is available, false if already registered in database
 */
function sms_autoreply_hook_keyword_isavail($keyword)
{
	$keyword = strtoupper(core_sanitize_alphanumeric($keyword));

	$db_query = "SELECT autoreply_id FROM " . _DB_PREF_ . "_featureAutoreply WHERE autoreply_keyword=?";
	if (dba_num_rows($db_query, [$keyword])) {

		return false;
	}

	return true;
}

/**
 * Implementations of hook recvsms_process()
 *
 * @param string $sms_datetime date and time when incoming sms inserted to playsms
 * @param string $sms_sender sender on incoming sms
 * @param string $autoreply_keyword check if keyword is for sms_autoreply
 * @param string $autoreply_param get parameters from incoming sms
 * @param string $sms_receiver receiver number that is receiving incoming sms
 * @param string $smsc SMSC
 * @param string $raw_message Original SMS
 * @return array array of keyword owner uid and status
 */
function sms_autoreply_hook_recvsms_process($sms_datetime, $sms_sender, $autoreply_keyword, $autoreply_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	$ret = [];

	$uid = 0;
	$status = false;

	$autoreply_keyword = strtoupper(core_sanitize_alphanumeric($autoreply_keyword));
	$autoreply_param = trim($autoreply_param);

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureAutoreply WHERE autoreply_keyword=?";
	$db_result = dba_query($db_query, [$autoreply_keyword]);
	if ($db_row = dba_fetch_array($db_result)) {
		$uid = $db_row['uid'];
		$smsc = gateway_decide_smsc($smsc, $db_row['smsc']);
		if (sms_autoreply_handle($db_row, $sms_datetime, $sms_sender, $sms_receiver, $autoreply_keyword, $autoreply_param, $smsc, $raw_message)) {
			$status = true;
		}
	}

	$ret['uid'] = $uid;
	$ret['status'] = $status;

	return $ret;
}

/**
 * Handle incoming SMS to this plugin
 * 
 * @param array $list
 * @param string $sms_datetime
 * @param string $sms_sender
 * @param string $sms_receiver
 * @param string $autoreply_keyword
 * @param string $autoreply_param
 * @param string $smsc
 * @param string $raw_message
 * @return bool
 */
function sms_autoreply_handle($list, $sms_datetime, $sms_sender, $sms_receiver, $autoreply_keyword, $autoreply_param = '', $smsc = '', $raw_message = '')
{
	$autoreply_keyword = strtoupper($autoreply_keyword);
	$autoreply_param = strtoupper($autoreply_param);
	if (!($sms_sender && $autoreply_keyword)) {

		return false;
	}

	$request = $autoreply_keyword . " " . $autoreply_param;
	$param = preg_split('/[\s]+/', $request);
	$autoreply_scenario_param_list = "";
	$db_argv = [$list['autoreply_id']];
	for ($i = 1; $i < 7; $i++) {
		if (isset($param[$i]) && $param[$i]) {
			$autoreply_scenario_param_list .= "AND autoreply_scenario_param" . $i . "=?";
			$db_argv[] = $param[$i];
		}
	}
	$db_query = "
		SELECT autoreply_scenario_result FROM " . _DB_PREF_ . "_featureAutoreply_scenario 
		WHERE autoreply_id=? " . $autoreply_scenario_param_list;

	$db_result = dba_query($db_query, $db_argv);
	$db_row = dba_fetch_array($db_result);
	if ($autoreply_scenario_result = $db_row['autoreply_scenario_result']) {
		$c_username = user_uid2username($list['uid']);
		$unicode = core_detect_unicode($autoreply_scenario_result);
		$autoreply_scenario_result = addslashes($autoreply_scenario_result);
		list($ret, $to, $smslog_id, $queue) = sendsms_helper($c_username, $sms_sender, $autoreply_scenario_result, 'text', $unicode, $smsc);

		return isset($ret[0]) && $ret[0] === true ? true : false;
	}

	return false;
}

/**
 * Check for valid ID
 * 
 * @param int $id
 * @return bool
 */
function sms_autoreply_check_id($id)
{
	return core_check_id($id, _DB_PREF_ . '_featureAutoreply', 'autoreply_id');
}