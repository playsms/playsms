<?php

defined('_SECURE_') or die('Forbidden');

// hook_sendsms
// called by main sms sender
// return true for success delivery
// $sms_sender	: sender mobile number
// $sms_footer		: sender sms footer or sms sender ID
// $sms_to		: destination sms number
// $sms_msg		: sms message tobe delivered
// $gpid		: group phonebook id (optional)
// $uid			: sender User ID
// $smslog_id		: sms ID
function uplink_hook_sendsms($sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	// global $plugin_config;   // global all variables needed, eg: varibles from config.php
	// ...
	// ...
	// return true or false
	// return $ok;
	global $plugin_config;
	$sms_sender = stripslashes($sms_sender);
	$sms_footer = ( $sms_footer ? $sms_footer : stripslashes($sms_footer) );
	$sms_msg = stripslashes($sms_msg) . $sms_footer;
	$ok = false;
	if ($sms_to && $sms_msg) {
		$nofooter = 0;
		if ($plugin_config['uplink']['try_disable_footer']) {
			$nofooter = 1;
		}
		if ($unicode) {
			// fixme anton - this isn't right, encoding should be done in master, not locally
			/*
			  if (function_exists('mb_convert_encoding')) {
			  $sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
			  }
			 */
			$unicode = 1;
		}
		// fixme anton - from playSMS v0.9.5.1 references to input.php replaced with index.php?app=webservices
		// I should add autodetect, if its below v0.9.5.1 should use input.php
		$query_string = "index.php?app=webservices&u=" . $plugin_config['uplink']['username'] . "&h=" . $plugin_config['uplink']['token'] . "&ta=pv&to=" . urlencode($sms_to) . "&from=" . urlencode($sms_sender) . "&type=$sms_type&msg=" . urlencode($sms_msg) . "&unicode=" . $unicode . "&nofooter=" . $nofooter;
		$url = $plugin_config['uplink']['master'] . "/" . $query_string;
		if ($additional_param = $plugin_config['uplink']['additional_param']) {
			$additional_param = "&" . $additional_param;
		}
		$url .= $additional_param;
		$url = str_replace("&&", "&", $url);
		logger_print($url, 3, "uplink outgoing");
		$responses = trim(file_get_contents($url));
		if ($responses) {
			$up_id = 0;
			$response = explode(' ', $responses);
			$response_data = explode(',', $response[1]);
			if ($response[0] == "OK") {
				$remote_smslog_id = $response_data[0];
				$remote_queue_code = $response_data[1];
				$dst = $response_data[2];
				if ($remote_smslog_id || ($remote_queue_code && $dst)) {
					$db_query = "
						INSERT INTO " . _DB_PREF_ . "_gatewayUplink (up_local_smslog_id,up_remote_smslog_id,up_status,up_remote_queue_code,up_dst)
						VALUES ('$smslog_id','$remote_smslog_id','0','$remote_queue_code','$dst')";
					if ($up_id = @dba_insert_id($db_query)) {
						$ok = true;
					}
				}
			}
			logger_print("smslog_id:" . $smslog_id . " up_id:" . $up_id . " status:" . $response[0] . " remote_smslog_id:" . $response_data[0] . " remote_queue_code:" . $response_data[1] . " dst:" . $dst, 2, "uplink outgoing");
		} else {
			logger_print("smslog_id:" . $smslog_id . " no response", 2, "uplink outgoing");
		}
	}
	if ($ok && ($remote_smslog_id || $remote_queue_code)) {
		$p_status = 0;
	} else {
		$p_status = 2;
	}
	dlr($smslog_id, $uid, $p_status);
	return $ok;
}

// hook_getsmsstatus
// called by index.php?app=menu&inc=daemon (periodic daemon) to set sms status
// no returns needed
// $p_datetime	: first sms delivery datetime
// $p_update	: last status update datetime
function uplink_hook_getsmsstatus($gpid = 0, $uid = "", $smslog_id = "", $p_datetime = "", $p_update = "") {
	// global $plugin_config;
	// p_status :
	// 0 = pending
	// 1 = delivered
	// 2 = failed
	// dlr($smslog_id,$uid,$p_status);
	global $plugin_config;
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayUplink WHERE up_local_smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$local_smslog_id = $db_row['up_local_smslog_id'];
		$remote_smslog_id = $db_row['up_remote_smslog_id'];
		$remote_queue_code = $db_row['up_remote_queue_code'];
		$dst = $db_row['up_dst'];
		if ($local_smslog_id && ($remote_smslog_id || ($remote_queue_code && $dst))) {
			// fixme anton - from playSMS v0.9.6 references to input.php replaced with index.php?app=webservices
			// I should add autodetect, if its below v0.9.6 should use input.php
			if ($remote_smslog_id) {
				$query_string = "index.php?app=webservices&u=" . $plugin_config['uplink']['username'] . "&h=" . $plugin_config['uplink']['token'] . "&ta=ds&smslog_id=" . $remote_smslog_id;
			} else {
				$query_string = "index.php?app=webservices&u=" . $plugin_config['uplink']['username'] . "&h=" . $plugin_config['uplink']['token'] . "&ta=ds&queue=" . $remote_queue_code . "&dst=" . $dst;
			}
			$url = $plugin_config['uplink']['master'] . "/" . $query_string;
			$response = trim(@implode('', file($url)));
			// logger_print("r:" . $response, 2, "uplink_hook_getsmsmstatus");
			$r = str_getcsv($response, ';', '"', "\\");
			if (($r[0] == 'ERR 400') || ($r[0] == 'ERR 402')) {
				$p_status = 2;
				dlr($local_smslog_id, $uid, $p_status);
			} else {
				if ($p_status = (int) $r[6]) {
					dlr($local_smslog_id, $uid, $p_status);
				}
			}
		}
	}
}

?>