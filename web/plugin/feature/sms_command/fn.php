<?php
defined('_SECURE_') or die('Forbidden');

/*
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword
 *   checkavailablekeyword() will insert keyword for checking to the hook here
 * @return
 *   TRUE if keyword is available
 */
function sms_command_hook_checkavailablekeyword($keyword)
{
	$ok = true;
	$db_query = "SELECT command_id FROM "._DB_PREF_."_featureCommand WHERE command_keyword='$keyword'";
	if ($db_result = dba_num_rows($db_query))
	{
		$ok = false;
	}
	return $ok;
}

/*
 * Implementations of hook setsmsincomingaction()
 *
 * @param $sms_datetime
 *   date and time when incoming sms inserted to playsms
 * @param $sms_sender
 *   sender on incoming sms
 * @param $command_keyword
 *   check if keyword is for sms_command
 * @param $command_param
 *   get parameters from incoming sms
 * @param $sms_receiver
 *   receiver number that is receiving incoming sms
 * @return $ret
 *   array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_command_hook_setsmsincomingaction($sms_datetime,$sms_sender,$command_keyword,$command_param='',$sms_receiver='',$raw_message='')
{
	$ok = false;
	$db_query = "SELECT uid,command_id FROM "._DB_PREF_."_featureCommand WHERE command_keyword='$command_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result))
	{
		$c_uid = $db_row['uid'];
		if (sms_command_handle($c_uid,$sms_datetime,$sms_sender,$sms_receiver,$command_keyword,$command_param,$raw_message))
		{
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

function sms_command_handle($c_uid,$sms_datetime,$sms_sender,$sms_receiver,$command_keyword,$command_param='',$raw_message='') {
	global $plugin_config;
	$ok = false;
	$command_keyword = strtoupper(trim($command_keyword));
	$command_param = trim($command_param);
	$db_query = "SELECT command_exec,uid,command_return_as_reply FROM "._DB_PREF_."_featureCommand WHERE command_keyword='$command_keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$command_exec = $db_row['command_exec'];
	$command_return_as_reply = $db_row['command_return_as_reply'];
	$username   = uid2username($db_row['uid']);
	if ($command_keyword && $command_exec && $username) {
		$sms_datetime = core_display_datetime($sms_datetime);
		$command_exec = str_replace("{SMSDATETIME}","\"$sms_datetime\"",$command_exec);
		$command_exec = str_replace("{SMSSENDER}","\"$sms_sender\"",$command_exec);
		$command_exec = str_replace("{COMMANDKEYWORD}","\"$command_keyword\"",$command_exec);
		$command_exec = str_replace("{COMMANDPARAM}","\"$command_param\"",$command_exec);
		$command_exec = str_replace("{COMMANDRAW}","\"$raw_message\"",$command_exec);
		$command_exec = $plugin_config['feature']['sms_command']['bin']."/".$db_row['uid']."/".$command_exec;
		logger_print("command_exec:".$command_exec, 3, "sms command");
		$command_output = shell_exec(stripslashes($command_exec));
		if ($command_return_as_reply == 1) {
			$unicode = core_detect_unicode($command_output);
			if ($command_output = addslashes(trim($command_output))) {
				logger_print("command_output:".$command_output, 3, "sms command");
				sendsms($username, $sms_sender, $command_output, 'text', $unicode);
			} else {
				logger_print("command_output is empty", 3, "sms command");
			}
		}
		$ok = true;
	}
	return $ok;
}

?>