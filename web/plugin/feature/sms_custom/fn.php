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
 * @param string $keyword
 *        Keyword
 * @param string $sms_receiver
 *        Receiver number
 * @return boolean TRUE if keyword is available
 */
function sms_custom_hook_keyword_isavail($keyword, $sms_receiver) {
	$found = FALSE;
	
	$keyword = trim(strtoupper($keyword));
	$sms_receiver = trim($sms_receiver);
	
	// check with receiver number empty, a catchall
	$db_query = "SELECT custom_id FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword LIKE '$keyword %' OR custom_keyword LIKE '% $keyword' OR custom_keyword LIKE '% $keyword %') AND sms_receiver=''";
	if ($db_result = dba_num_rows($db_query)) {
		$found = TRUE;
	}
	
	// if no cathall
	if (!$found) {
		if ($sms_receiver) {
			$db_query = "SELECT custom_id FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword LIKE '$keyword %' OR custom_keyword LIKE '% $keyword' OR custom_keyword LIKE '% $keyword %') AND sms_receiver='$sms_receiver'";
		} else {
			$db_query = "SELECT custom_id FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword LIKE '$keyword %' OR custom_keyword LIKE '% $keyword' OR custom_keyword LIKE '% $keyword %')";
		}
		if ($db_result = dba_num_rows($db_query)) {
			$found = TRUE;
		}
	}
	
	// found means not available
	$ret = !$found;
	
	return $ret;
}

/**
 * Implementations of hook recvsms_process()
 *
 * @param string $sms_datetime
 *        date and time when incoming sms inserted to playsms
 * @param string $sms_sender
 *        sender on incoming sms
 * @param string $keyword
 *        check if keyword is for sms_custom
 * @param string $custom_param
 *        get parameters from incoming sms
 * @param string $sms_receiver
 *        receiver number that is receiving incoming sms
 * @param string $smsc
 *        SMSC ID
 * @param string $raw_message
 *        raw incoming message
 * @return array $ret array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_custom_hook_recvsms_process($sms_datetime, $sms_sender, $keyword, $custom_param = '', $sms_receiver = '', $smsc = '', $raw_message = '') {
	$ok = FALSE;
	
	// match keyword with receiver number
	if ($sms_receiver) {
		$db_query = "SELECT uid FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword LIKE '$keyword %' OR custom_keyword LIKE '% $keyword' OR custom_keyword LIKE '% $keyword %') AND sms_receiver='$sms_receiver'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			$c_uid = $db_row['uid'];
		}
	}
	
	// look for matching with catchall, if found it will override above matches
	$db_query = "SELECT uid FROM " . _DB_PREF_ . "_featureCustom WHERE (custom_keyword LIKE '$keyword %' OR custom_keyword LIKE '% $keyword' OR custom_keyword LIKE '% $keyword %') AND sms_receiver = ''";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$c_uid = $db_row['uid'];
	}
	
	if ($c_uid) {
		if (sms_custom_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $custom_keyword, $custom_param, $smsc, $raw_message)) {
			$ok = TRUE;
		}
		$ret['uid'] = $c_uid;
	}
	
	$ret['status'] = $ok;
	
	return $ret;
}

function sms_custom_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $custom_keyword, $custom_param = '', $smsc = '', $raw_message = '') {
	$ok = false;
	$custom_keyword = strtoupper(trim($custom_keyword));
	$custom_param = trim($custom_param);
	$db_query = "SELECT custom_url,uid,service_name,sms_receiver,custom_return_as_reply FROM " . _DB_PREF_ . "_featureCustom WHERE custom_keyword='$custom_keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$service_name = htmlspecialchars_decode($db_row['service_name']);
	$sms_receiver = $db_row['sms_receiver'];
	$custom_url = htmlspecialchars_decode($db_row['custom_url']);
	$username = user_uid2username($db_row['uid']);
	$custom_return_as_reply = $db_row['custom_return_as_reply'];
	if ($custom_keyword && $custom_url && $username) {
		$sms_datetime = core_display_datetime($sms_datetime);
		$custom_url = str_replace("{SERVICENAME}", urlencode($service_name), $custom_url);
		$custom_url = str_replace("{SMSDATETIME}", urlencode($sms_datetime), $custom_url);
		$custom_url = str_replace("{SMSSENDER}", urlencode($sms_sender), $custom_url);
		$custom_url = str_replace("{SMSRECEIVER}", urlencode($sms_receiver), $custom_url);
		$custom_url = str_replace("{CUSTOMKEYWORD}", urlencode($custom_keyword), $custom_url);
		$custom_url = str_replace("{CUSTOMPARAM}", urlencode($custom_param), $custom_url);
		$custom_url = str_replace("{CUSTOMRAW}", urlencode($raw_message), $custom_url);
		logger_print("custom_url:[" . $custom_url . "]", 3, "sms_custom_handle");
		
		$parsed_url = parse_url($custom_url);
		
		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header' => "Content-type: application/x-www-form-urlencoded\r\n",
				'content' => $parsed_url['query'] 
			) 
		);
		
		$context = stream_context_create($opts);
		
		$server_url = explode('?', $custom_url);
		
		$returns = file_get_contents($server_url[0], false, $context);
		if ($custom_return_as_reply == 1) {
			if ($returns = trim($returns)) {
				$unicode = core_detect_unicode($returns);
				$returns = addslashes($returns);
				logger_print("returns:[" . $returns . "]", 3, "sms_custom_handle");
				sendsms_helper($username, $sms_sender, $returns, 'text', $unicode, $smsc);
			} else {
				logger_print("returns empty", 3, "sms_custom_handle");
			}
		}
		$ok = true;
	}
	return $ok;
}
