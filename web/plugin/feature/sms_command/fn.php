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

function sms_command_handle($c_uid,$sms_datetime,$sms_sender,$sms_receiver,$command_keyword,$command_param='',$raw_message='')
{
	global $datetime_now, $plugin_config;
	$ok = false;
	$db_query = "SELECT command_id, command_exec, uid, command_return_as_reply, with_alarm, with_answer, command_msg FROM "._DB_PREF_."_featureCommand WHERE command_keyword='$command_keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$with_alarm = $db_row['with_alarm'];
	$command_id = $db_row['command_id'];
	$with_answer = $db_row['with_answer'];
	$command_msg = $db_row['command_msg'];
	$command_author_id = $db_row['uid'];
	$command_exec = $db_row['command_exec'];
	$sms_datetime = core_display_datetime($sms_datetime);
	$command_exec = str_replace("{SMSDATETIME}","\"$sms_datetime\"",$command_exec);
	$command_exec = str_replace("{SMSSENDER}","\"$sms_sender\"",$command_exec);
	$command_exec = str_replace("{COMMANDKEYWORD}","\"$command_keyword\"",$command_exec);
	$command_exec = str_replace("{COMMANDPARAM}","\"$command_param\"",$command_exec);
	$command_exec = str_replace("{COMMANDRAW}","\"$raw_message\"",$command_exec);
	$command_exec = "cd ".$plugin_config['feature']['sms_command']['bin']."/;".$command_exec;
	$command_output = shell_exec(stripslashes($command_exec));
        
    $username   = uid2username($db_row['uid']);
    if ($db_row['command_return_as_reply'] == 1) {
		sendsms_pv($username, $sms_sender, $command_output, 'text', 0);
	}
	$db_query = "
	INSERT INTO "._DB_PREF_."_featureCommand_log
	(sms_sender,command_log_datetime,command_log_keyword,command_log_exec, command_log_output)  
	VALUES
	('$sms_sender','$datetime_now','$command_keyword','$command_exec', '$command_output')
    ";
	if ($new_id = @dba_insert_id($db_query))
	{
		if($with_answer){
			$command_msg = str_replace("{COMMANDOUTPUT}",$command_output,$command_msg);
			$command_author_username= uid2username($command_author_id);
			$unicode = 0;
			list($ok, $to, $smslog_id) = sendsms_pv($command_author_username, $sms_sender, $command_msg, 'text', $unicode);
		}
		
		if($with_alarm){
			$command_output = trim($command_output);
			//Verifiy is command output is number!
			if(validatevaluetype_int($command_output)){
				
				if (function_exists('sendsms_pv')) {
					$unicode = 0;
				}
				
				$db_query = "SELECT alarm_id, alarm_msg, alarm_min_value, alarm_max_value,uid FROM "._DB_PREF_."_featureCommand_Alarm WHERE command_id='$command_id'";
				$db_result = dba_query($db_query);
				
				while ($db_row = dba_fetch_array($db_result))
				{
					$command_author = uid2username($db_row['uid']);
					$alarm_id = $db_row['alarm_id'];
					$alarm_msg = $db_row['alarm_msg'];
					$min_value = $db_row['alarm_min_value'];
					$max_value = $db_row['alarm_max_value'];
					$output_value = intval($command_output);
					
					if( ($output_value < $min_value) || ($output_value > $max_value)){
						
							logger_print('COMMAND OUTPUT: OUT OF RANGE!',3,'');
							//SEND SMS!
							$db_query_contacts = "SELECT contact_number FROM "._DB_PREF_."_featureCommand_Alarm_contacts WHERE alarm_id='$alarm_id'";
							$db_result_contacts = dba_query($db_query_contacts);

							while ($db_row_contacts = dba_fetch_array($db_result_contacts))
							{
								$contact_number = $db_row_contacts['contact_number'];
								$unicode = 0;
								list($ok, $to, $smslog_id) = sendsms_pv($command_author, $contact_number, $alarm_msg, 'text', $unicode);
							}
							
							$db_query_groups = "SELECT gpid FROM "._DB_PREF_."_featureCommand_Alarm_group_id WHERE alarm_id='$alarm_id'";
							$db_result_groups = dba_query($db_query_groups);
							//FOR EACH CONTACT, SEND
							while ($db_row_groups = dba_fetch_array($db_result_groups))
							{
								$gpid = $db_row_groups['gpid'];
								$unicode = 0;
								list($ok, $to, $smslog_id) = sendsms_bc($command_author, $gpid, $alarm_msg, 'text', $unicode);
							}
					}else{
						logger_print('COMMAND OUTPUT: NORMAL!',3,'');
					}
				}	
			}else{
					logger_print('COMMAND OUTPUT: OUTPUT VALUE ISN\'T INTEGER!',3,'');
			}

		}
		$ok = true;
	}else{
		$error_message = "An error occured after the execution of your command. Sorry for the inconvenient.";
		$unicode = 0;
		list($ok, $to, $smslog_id) = sendsms_pv("admin", $sms_sender, $error_message, 'text', $unicode);
		$ok = true;	
	}
	
	return $ok;
}

function checkavailablealarmname($alarm_name){
	$ok = true;
	$db_query = "SELECT * FROM "._DB_PREF_."_featureCommand_Alarm WHERE alarm_name='$alarm_name'";
	if ($db_result = dba_num_rows($db_query))
	{
		$ok = false;
	}
	return $ok;		
}

function validatevaluetype_int($value){
	$ok = false;
	
	$value_int = intval($value);
	$value_int_str = "$value_int";
	if($value_int_str == $value)
		$ok = true;
	
	return $ok;
}

function getAlarmNumbers_output($alarm_id){
	$db_query = "SELECT * FROM "._DB_PREF_."_featureCommand_Alarm_contacts WHERE alarm_id=$alarm_id";	
	
	$str_return = '';
	$db_result = dba_query($db_query);
	if ($db_result)
	{	
		while ($db_row = dba_fetch_array($db_result))
		{
			$str_return .= $db_row['contact_number'] . '<br />';
		}
		
	}else{
		$str_return =  "undefined";
	}
	
	return $str_return;
}

function sms_command_hook_call($requests){
	global $apps_path, $http_path, $core_config, $datetime_now;
	
	$called_from_hook_call = true;
	
	$fn = $apps_path['plug'].'/feature/sms_command/callback.php';
	include $fn;
	
	$access = $requests['access'];
	if ($access == 'callback') {
		$fn = $apps_path['plug'].'/feature/sms_command/callback.php';
		logger_print("start load:".$fn, 3, "sms command call");
		include $fn;
		logger_print("end load callback", 3, "sms command call");
	}
}	

?>
