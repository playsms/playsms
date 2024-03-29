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
 * @param $keyword keyword_isavail()
 *        will insert keyword for checking to the hook here
 * @return TRUE if keyword is available
 */
function sms_autoreply_hook_keyword_isavail($keyword)
{
	$ok = true;

	$db_query = "SELECT autoreply_id FROM " . _DB_PREF_ . "_featureAutoreply WHERE autoreply_keyword=?";
	if ($db_result = dba_num_rows($db_query, [$keyword])) {
		$ok = false;
	}

	return $ok;
}

/**
 * Implementations of hook recvsms_process()
 *
 * @param $sms_datetime date
 *        and time when incoming sms inserted to playsms
 * @param $sms_sender sender
 *        on incoming sms
 * @param $autoreply_keyword check
 *        if keyword is for sms_autoreply
 * @param $autoreply_param get
 *        parameters from incoming sms
 * @param $sms_receiver receiver
 *        number that is receiving incoming sms
 * @return $ret array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_autoreply_hook_recvsms_process($sms_datetime, $sms_sender, $autoreply_keyword, $autoreply_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	$ok = false;

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureAutoreply WHERE autoreply_keyword=?";
	$db_result = dba_query($db_query, [$autoreply_keyword]);
	if ($db_row = dba_fetch_array($db_result)) {
		$c_uid = $db_row['uid'];
		$autoreply_id = $db_row['autoreply_id'];
		$smsc = gateway_decide_smsc($smsc, $db_row['smsc']);
		if (sms_autoreply_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $autoreply_id, $autoreply_keyword, $autoreply_param, $smsc, $raw_message)) {
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;

	return $ret;
}

function sms_autoreply_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $autoreply_id, $autoreply_keyword, $autoreply_param = '', $smsc = '', $raw_message = '')
{
	$ok = false;

	$autoreply_keyword = strtoupper(trim($autoreply_keyword));
	$autoreply_param = strtoupper(trim($autoreply_param));
	$autoreply_request = $autoreply_keyword . " " . $autoreply_param;
	$array_autoreply_requests = preg_split('/[\s]+/', $autoreply_request);
	$autoreply_scenario_param_list = '';
	$db_argv = [];
	$i = 0;
	foreach ( $array_autoreply_requests as $request ) {
		$i++;
		$autoreply_part[$i] = trim($request[$i]);
		$tmp_autoreply_request .= trim($request[$i]) . " ";
		$autoreply_scenario_param_list .= "autoreply_scenario_param" . $i . "=? AND ";
		$db_argv[] = $autoreply_part[$i];
	}
	$autoreply_request = trim($tmp_autoreply_request);
	if ($autoreply_scenario_param_list) {
		$autoreply_scenario_param_list = substr($autoreply_scenario_param_list, 0, -4); // remove trailing AND
		$autoreply_scenario_param_list = trim($autoreply_scenario_param_list);
	}
	$db_query = "
		SELECT autoreply_scenario_result FROM " . _DB_PREF_ . "_featureAutoreply_scenario 
		WHERE $autoreply_scenario_param_list AND autoreply_id=?";
	$db_argv[] = $autoreply_id;
	$db_result = dba_query($db_query, $db_argv);
	$db_row = dba_fetch_array($db_result);
	if ($autoreply_scenario_result = $db_row['autoreply_scenario_result']) {
		$ok = false;
		$c_username = user_uid2username($c_uid);
		$unicode = core_detect_unicode($autoreply_scenario_result);
		$autoreply_scenario_result = addslashes($autoreply_scenario_result);
		list($ok, $to, $smslog_id, $queue) = sendsms_helper($c_username, $sms_sender, $autoreply_scenario_result, 'text', $unicode, $smsc);
		$ok = $ok[0];
	}

	return $ok;
}