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
function sms_custom_hook_checkavailablekeyword($keyword) {
	$ok = true;
	$db_query = "SELECT custom_id FROM "._DB_PREF_."_featureCustom WHERE custom_keyword='$keyword'";
	if ($db_result = dba_num_rows($db_query)) {
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
function sms_custom_hook_setsmsincomingaction($sms_datetime,$sms_sender,$custom_keyword,$custom_param='',$sms_receiver='',$smsc='',$raw_message='') {
	$ok = false;
	$db_query = "SELECT uid,custom_id FROM "._DB_PREF_."_featureCustom WHERE custom_keyword='$custom_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$c_uid = $db_row['uid'];
		if (sms_custom_handle($c_uid,$sms_datetime,$sms_sender,$sms_receiver,$custom_keyword,$custom_param,$smsc,$raw_message)) {
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

function sms_custom_handle($c_uid,$sms_datetime,$sms_sender,$sms_receiver,$custom_keyword,$custom_param='',$smsc='',$raw_message='') {
	$ok = false;
	$custom_keyword = strtoupper(trim($custom_keyword));
	$custom_param = trim($custom_param);
	$db_query = "SELECT custom_url,uid,custom_return_as_reply FROM "._DB_PREF_."_featureCustom WHERE custom_keyword='$custom_keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$custom_url = htmlspecialchars_decode($db_row['custom_url']);
	$username = user_uid2username($db_row['uid']);
	$custom_return_as_reply = $db_row['custom_return_as_reply'];
	if ($custom_keyword && $custom_url && $username) {
		$sms_datetime = core_display_datetime($sms_datetime);
		$custom_url = str_replace("{SMSDATETIME}",urlencode($sms_datetime),$custom_url);
		$custom_url = str_replace("{SMSSENDER}",urlencode($sms_sender),$custom_url);
		$custom_url = str_replace("{CUSTOMKEYWORD}",urlencode($custom_keyword),$custom_url);
		$custom_url = str_replace("{CUSTOMPARAM}",urlencode($custom_param),$custom_url);
		$custom_url = str_replace("{CUSTOMRAW}",urlencode($raw_message),$custom_url);
		logger_print("custom_url:".$custom_url, 3, "sms custom");
		
		$parsed_url = parse_url($custom_url);
		
		$opts = array('http' =>
				array(
					'method'  => 'POST',
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'content' => $parsed_url['query']
				)
		);
		
		$context  = stream_context_create($opts);
		
		$server_url = explode('?', $custom_url);
		
		$returns = file_get_contents($server_url[0], false, $context);
		if ($custom_return_as_reply == 1) {
			if ($returns = trim($returns)) {
				$unicode = core_detect_unicode($returns);
				$returns = addslashes($returns);
				logger_print("returns:".$returns, 3, "sms custom");
				sendsms_helper($username, $sms_sender, $returns, 'text', $unicode, $smsc);
			} else {
				logger_print("returns empty", 3, "sms custom");
			}
		}
		$ok = true;
	}
	return $ok;
}
