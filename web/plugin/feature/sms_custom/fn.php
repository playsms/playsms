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
function sms_custom_hook_checkavailablekeyword($keyword)
{
	$ok = true;
	$db_query = "SELECT custom_id FROM "._DB_PREF_."_featureCustom WHERE custom_keyword='$keyword'";
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
 * @param $custom_keyword
 *   check if keyword is for sms_custom
 * @param $custom_param
 *   get parameters from incoming sms
 * @param $sms_receiver
 *   receiver number that is receiving incoming sms
 * @return $ret
 *   array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_custom_hook_setsmsincomingaction($sms_datetime,$sms_sender,$custom_keyword,$custom_param='',$sms_receiver='',$raw_message='')
{
	$ok = false;
	$db_query = "SELECT uid,custom_id FROM "._DB_PREF_."_featureCustom WHERE custom_keyword='$custom_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result))
	{
		$c_uid = $db_row['uid'];
		if (sms_custom_handle($c_uid,$sms_datetime,$sms_sender,$sms_receiver,$custom_keyword,$custom_param,$raw_message))
		{
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

function sms_custom_handle($c_uid,$sms_datetime,$sms_sender,$sms_receiver,$custom_keyword,$custom_param='',$raw_message='')
{
	global $datetime_now;
	$ok = false;
	$db_query = "SELECT custom_url,uid,custom_return_as_reply FROM "._DB_PREF_."_featureCustom WHERE custom_keyword='$custom_keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$custom_url = $db_row['custom_url'];
	$sms_datetime = core_display_datetime($sms_datetime);
	$custom_url = str_replace("{SMSDATETIME}",urlencode($sms_datetime),$custom_url);
	$custom_url = str_replace("{SMSSENDER}",urlencode($sms_sender),$custom_url);
	$custom_url = str_replace("{CUSTOMKEYWORD}",urlencode($custom_keyword),$custom_url);
	$custom_url = str_replace("{CUSTOMPARAM}",urlencode($custom_param),$custom_url);
	$custom_url = str_replace("{CUSTOMRAW}",urlencode($raw_message),$custom_url);
        $username   = uid2username($db_row['uid']);
        $debug      = dba_query("INSERT INTO " . _DB_PREF_ . "_toolsDebug (value)VALUES('$username')");
	$url = parse_url($custom_url);
	if (!$url['port'])
	{
		$url['port'] = 80;
	}
	// fixme anton -deprecated when using PHP5
	//$connection = fsockopen($url['host'],$url['port'],&$error_number,&$error_description,60);
        //fixme Edward, change to file_get_contents
        $returns    = file_get_contents($custom_url);
	if($returns) {
                //fixme Edward, change to file_get_contents
		//socket_set_blocking($connection, false);
		//fputs($connection, "GET $custom_url HTTP/1.0\r\n\r\n");
                $username   = uid2username($db_row['uid']);
                if ($db_row['custom_return_as_reply'] == 1) {
                	sendsms_pv($username, $sms_sender, $returns, 'text', 0);
                }
		$db_query = "
	    INSERT INTO "._DB_PREF_."_featureCustom_log
	    (sms_sender,custom_log_datetime,custom_log_keyword,custom_log_url) 
	    VALUES
	    ('$sms_sender','$datetime_now','$custom_keyword','$custom_url')
	";
		if ($new_id = @dba_insert_id($db_query))
		{
			$ok = true;
		}
	}
	return $ok;
}

?>