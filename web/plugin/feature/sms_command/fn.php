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
 * @param string $keyword keyword_isavail() will insert keyword for checking to the hook here
 * @return bool true if keyword is available
 */
function sms_command_hook_keyword_isavail($keyword)
{
	$keyword = strtoupper(core_sanitize_alphanumeric($keyword));

	$db_query = "SELECT command_id FROM " . _DB_PREF_ . "_featureCommand WHERE command_keyword=?";
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
 * @param string $command_keyword check if keyword is for sms_command
 * @param string $command_param get parameters from incoming sms
 * @param string $sms_receiver receiver number that is receiving incoming sms
 * @param string $smsc SMSC
 * @param string $raw_message Original SMS
 * @return array array of keyword owner uid and status, true if incoming sms handled
 */
function sms_command_hook_recvsms_process($sms_datetime, $sms_sender, $command_keyword, $command_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	$ret = [];

	$uid = 0;
	$status = false;

	$command_keyword = strtoupper(core_sanitize_alphanumeric($command_keyword));
	$command_param = trim($command_param);

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCommand WHERE command_keyword=?";
	$db_result = dba_query($db_query, [$command_keyword]);
	if ($db_row = dba_fetch_array($db_result)) {
		$uid = $db_row['uid'];
		$smsc = gateway_decide_smsc($smsc, $db_row['smsc']);
		if (sms_command_handle($db_row, $sms_datetime, $sms_sender, $sms_receiver, $command_keyword, $command_param, $smsc, $raw_message)) {
			$status = true;
		}
	}

	$ret['uid'] = $uid;
	$ret['status'] = $status;

	return $ret;
}

function sms_command_handle($list, $sms_datetime, $sms_sender, $sms_receiver, $command_keyword, $command_param = '', $smsc = '', $raw_message = '')
{
	global $plugin_config;

	$smsc = gateway_decide_smsc($smsc, $list['smsc']);

	if (!($sms_sender && $command_keyword)) {

		return false;
	}

	$db_query = "SELECT command_exec,uid,command_return_as_reply FROM " . _DB_PREF_ . "_featureCommand WHERE command_keyword=?";
	$db_result = dba_query($db_query, [$command_keyword]);
	$db_row = dba_fetch_array($db_result);
	$command_exec = $db_row['command_exec'];
	$command_return_as_reply = (int) $db_row['command_return_as_reply'];
	$username = user_uid2username($db_row['uid']);
	if (!($command_keyword && $command_exec && $username)) {

		return false;
	}

	$sms_datetime = core_display_datetime($sms_datetime);
	$command_exec = str_replace("{SMSDATETIME}", "\"$sms_datetime\"", $command_exec);
	$command_exec = str_replace("{SMSSENDER}", escapeshellarg($sms_sender), $command_exec);
	$command_exec = str_replace("{COMMANDKEYWORD}", escapeshellarg($command_keyword), $command_exec);
	$command_exec = str_replace("{COMMANDPARAM}", escapeshellarg($command_param), $command_exec);
	$command_exec = str_replace("{COMMANDRAW}", escapeshellarg($raw_message), $command_exec);
	$command_exec = str_replace("/", "", $command_exec);
	$command_exec = $plugin_config['sms_command']['bin'] . "/" . $db_row['uid'] . "/" . $command_exec;
	$command_exec = escapeshellcmd($command_exec);

	$command_exec = str_ireplace('..', '', $command_exec);
	$command_exec = str_ireplace('|', '', $command_exec);
	$command_exec = str_ireplace('&', '', $command_exec);
	$command_exec = str_ireplace('`', '', $command_exec);
	$command_exec = str_ireplace('nohup ', '', $command_exec);

	_log("command_exec:" . addslashes($command_exec), 3, "sms command");
	$command_output = shell_exec($command_exec);
	if ($command_return_as_reply === 1) {
		$unicode = core_detect_unicode($command_output);
		if ($command_output = addslashes(trim($command_output))) {
			_log("command_output:" . $command_output, 3, "sms command");
			sendsms_helper($username, $sms_sender, $command_output, 'text', $unicode, $smsc);
		} else {
			_log("command_output is empty", 3, "sms command");
		}
	}

	return true;
}

/**
 * Check for valid ID
 * 
 * @param int $id
 * @return bool
 */
function sms_command_check_id($id)
{
	return core_check_id($id, _DB_PREF_ . '_featureCommand', 'command_id');
}