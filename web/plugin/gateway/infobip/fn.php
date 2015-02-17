<?php
defined('_SECURE_') or die('Forbidden');

function infobip_hook_getsmsstatus($gpid = 0, $uid = "", $smslog_id = "", $p_datetime = "", $p_update = "") {
	global $plugin_config;
	
	list($c_sms_credit, $c_sms_status) = infobip_getsmsstatus($smslog_id);
	// pending
	$p_status = 0;
	if ($c_sms_status) {
		$p_status = $c_sms_status;
	}
	setsmsdeliverystatus($smslog_id, $uid, $p_status);
}

function infobip_hook_playsmsd() {
	global $plugin_config;

	// fetch every 60 seconds
	if (!core_playsmsd_timer(60)) {
		return;
	}

	if ($plugin_config['infobip']['dlr_nopush'] == '1') {
		// force to check p_status=1 (sent) as getsmsstatus only check for p_status=0 (pending)
		// $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status=0 OR p_status=1";
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE p_status='1' AND p_gateway='infobip'";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$uid = $db_row['uid'];
			$smslog_id = $db_row['smslog_id'];
			$p_datetime = $db_row['p_datetime'];
			$p_update = $db_row['p_update'];
			$gpid = $db_row['p_gpid'];
			core_hook('infobip', 'getsmsstatus', array(
				$gpid,
				$uid,
				$smslog_id,
				$p_datetime,
				$p_update 
			));
		}
	}
}

function infobip_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	$ok = false;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "infobip_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	if ($plugin_config['infobip']['module_sender']) {
		$sms_sender = $plugin_config['infobip']['module_sender'];
	}
	
	$sms_from = $sms_sender;
	$smsType = "&SMSText";
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	switch ($sms_type) {
		case "flash" :
			$sms_type = 1;
			break;
		case "text" :
		default :
			$sms_type = 0;
	}
	
	if ($unicode) {
		if (function_exists('mb_convert_encoding')) {
			$sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
		}
		$sms_msg = core_str2hex($sms_msg);
		$unicode = 8;
		$smsType = "&binary";
	}
	
	// fixme anton - if sms_from is not set in gateway_number and global number, we cannot pass it to infobip
	$set_sms_from = ($sms_from == $sms_sender ? '' : urlencode($sms_from));
	
	// query_string = "sendmsg?api_id=".$plugin_config['infobip']['api_id']."&user=".$plugin_config['infobip']['username']."&password=".$plugin_config['infobip']['password']."&to=".urlencode($sms_to)."&msg_type=$sms_type&text=".urlencode($sms_msg)."&unicode=".$unicode.$set_sms_from;
	$query_string = "sendsms/plain?user=" . $plugin_config['infobip']['username'] . "&password=" . $plugin_config['infobip']['password'];
	$query_string .= "&GSM=" . urlencode($sms_to) . $smsType . "=" . urlencode($sms_msg) . "&sender=" . $sms_from;
	$query_string .= "&IsFlash=" . $sms_type . "&DataCoding=" . $unicode;
	
	$url = $plugin_config['infobip']['send_url'] . "/" . $query_string;
	
	$dlr_nopush = $plugin_config['infobip']['dlr_nopush'];
	if ($dlr_nopush == '0') {
		$additional_param = "&nopush=0";
	} elseif ($dlr_nopush == '1') {
		$additional_param = "&nopush=1";
	}
	
	if ($additional_param = $plugin_config['infobip']['additional_param']) {
		$additional_param .= "&" . $additional_param;
	}
	
	$url .= $additional_param;
	$url = str_replace("&&", "&", $url);
	
	logger_print("url:" . $url, 3, "infobip outgoing");
	$xml = file_get_contents($url);
	
	$response = core_xml_to_array($xml);
	
	if ($response) {
		if ($response['result']['status'] == 0) {
			if ($apimsgid = trim($response['result']['messageid'])) {
				infobip_setsmsapimsgid($smslog_id, $apimsgid);
				list($c_sms_credit, $c_sms_status) = infobip_getsmsstatus($smslog_id);
				// pending
				$p_status = 0;
				if ($c_sms_status) {
					$p_status = $c_sms_status;
				}
			} else {
				// sent
				$p_status = 1;
			}
			logger_print("smslog_id:" . $smslog_id . " charge:" . $c_sms_credit . " p_status:" . $p_status . " response:" . $response['result']['status'], 2, "infobip outgoing");
		} elseif ($response['result']['status'] == -2) {
			logger_print("smslog_id:" . $smslog_id . " response:" . $response['result']['status'] . " NOT_ENOUGH_CREDIT", 2, "infobip outgoing");
		} else {
			// even when the response is not what we expected we still print it out for debug purposes
			$fd = str_replace("\n", " ", $fd);
			$fd = str_replace("\r", " ", $fd);
			logger_print("smslog_id:" . $smslog_id . " response:" . $response['result']['status'] . " UNKNOWN_CODE", 2, "infobip outgoing");
		}
		$ok = true;
	} else {
		logger_print("no response smslog_id:" . $smslog_id, 3, "infobip outgoing");
	}
	if (!$ok) {
		$p_status = 2;
	}
	dlr($smslog_id, $uid, $p_status);
	return $ok;
}

function infobip_hook_getsmsinbox() {
	// fixme anton - infobip will only receive incoming sms from callback url
	/*
	 * global $plugin_config; $handle = @opendir($plugin_config['infobip']['incoming_path']); while ($sms_in_file = @readdir($handle)) { if (eregi("^ERR.in",$sms_in_file) && !eregi("^[.]",$sms_in_file)) { $fn = $plugin_config['infobip']['incoming_path']."/$sms_in_file"; $tobe_deleted = $fn; $lines = @file ($fn); $sms_datetime = trim($lines[0]); $sms_sender = trim($lines[1]); $message = ""; for ($lc=2;$lc<count($lines);$lc++) { $message .= trim($lines[$lc]); } // collected: // $sms_datetime, $sms_sender, $message, $sms_receiver setsmsincomingaction($sms_datetime,$sms_sender,$message,$sms_receiver,'infobip'); @unlink($tobe_deleted); } }
	 */
}

function infobip_getsmsstatus($smslog_id) {
	global $plugin_config;
	
	// Be carefull nopush should be set to 1 and no Push url should be defined on infobip account !
	// infobip dlr url if defined overset this config
	if ($plugin_config['infobip']['dlr_nopush'] == '1') {
		$c_sms_status = 0;
		$c_sms_credit = 0;
		$db_query = "SELECT apimsgid FROM " . _DB_PREF_ . "_gatewayInfobip_apidata WHERE smslog_id='$smslog_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($apimsgid = $db_row['apimsgid']) {
			// $query_string = "getmsgcharge?api_id=".$plugin_config['infobip']['api_id']."&user=".$plugin_config['infobip']['username']."&password=".$plugin_config['infobip']['password']."&apimsgid=$apimsgid";
			$query_string = "pull?user=" . $plugin_config['infobip']['username'] . "&password=" . $plugin_config['infobip']['password'] . "&messageid=$apimsgid";
			// $url = $plugin_config['infobip']['send_url']."/".$query_string;
			$url = $plugin_config['infobip']['send_url'] . "/dr/" . $query_string;
			logger_print("smslog_id:" . $smslog_id . " apimsgid:" . $apimsgid . " url:" . $url, 2, "infobip getsmsstatus");
			$fd = @implode('', file($url));
			logger_print("fd: " . $fd, 3, "infobip debug");
			if ($fd != "NO_DATA") {
				// $response = explode(" ", $fd);
				// $err_code = trim ($response[1]);
				$credit = 0;
				// if ((strtoupper(trim($response[2])) == "CHARGE:")) {
				// $credit = intval(trim($response[3]));
				// }
				// $c_sms_credit = $credit;
				preg_match_all('/id=\"([0-9]+)\"/', $fd, $result);
				// print_r($result);
				// print "id:\t".$result[1][0]."\n";
				$apimsgid = $result[1][0];
				logger_print("apimsgid: " . $apimsgid, 3, "infobip debug");
				
				if (preg_match_all('/status=\"([A-Z]+)\"/', $fd, $result)) {
					// status = trim($response[5]);
					$status = $result[1][0];
					switch ($status) {
						case "DELIVERED" :
							$c_sms_status = 3;
							break; // delivered
						case "NOT_DELIVERED" :
							$c_sms_status = 2;
							break; // failed
						case "NOT_ENOUGH_CREDITS" :
							$c_sms_status = 2;
							break; // failed
					}
				}
				logger_print("smslog_id:" . $smslog_id . " apimsgid:" . $apimsgid . " charge:" . $credit . " status:" . $status . " sms_status:" . $c_sms_status, 2, "infobip getsmsstatus");
			}
		}
		return array(
			$c_sms_credit,
			$c_sms_status 
		);
	}
}

function infobip_setsmsapimsgid($smslog_id, $apimsgid) {
	if ($smslog_id && $apimsgid) {
		$db_query = "INSERT INTO " . _DB_PREF_ . "_gatewayInfobip_apidata (smslog_id,apimsgid) VALUES ('$smslog_id','$apimsgid')";
		$db_result = dba_query($db_query);
	}
}

function infobip_hook_call($requests) {
	// please note that we must globalize these 2 variables
	global $core_config, $plugin_config;
	$called_from_hook_call = true;
	$access = $requests['access'];
	
	if ($access == 'callback') {
		$fn = $core_config['apps_path']['plug'] . '/gateway/infobip/callback.php';
		logger_print("start load:" . $fn, 2, "infobip call");
		include $fn;
		logger_print("end load callback", 2, "infobip call");
	}
	
	if ($access == 'dlr') {
		$fn = $core_config['apps_path']['plug'] . '/gateway/infobip/dlr.php';
		logger_print("start load:" . $fn, 2, "infobip dlr call");
		include $fn;
		logger_print("end load callback", 2, "infobip dlr call");
	}
}
