<?php
defined('_SECURE_') or die('Forbidden');


function bulksms_hook_getsmsstatus($gpid = 0, $uid = "", $smslog_id = "", $p_datetime = "", $p_update = "") {
	global $plugin_config;
	list($c_sms_credit, $c_sms_status) = bulksms_getsmsstatus($smslog_id);
	// pending
	$p_status = 0;
	if ($c_sms_status) {
		$p_status = $c_sms_status;
	}
	dlr($smslog_id, $uid, $p_status);
}

function bulksms_hook_playsmsd() {
	// force to check p_status=1 (sent) as getsmsstatus only check for p_status=0 (pending)
	// $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE p_status=0 OR p_status=1";
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE p_status='1' AND p_gateway='bulksms'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$uid = $db_row['uid'];
		$smslog_id = $db_row['smslog_id'];
		$p_datetime = $db_row['p_datetime'];
		$p_update = $db_row['p_update'];
		$gpid = $db_row['p_gpid'];
		core_hook('bulksms', 'getsmsstatus', array(
			$gpid,
			$uid,
			$smslog_id,
			$p_datetime,
			$p_update 
		));
	}
}


function bulksms_sms_url($sms_to, $sms_class, $sms_msg, $sms_dca, $set_sms_from){
	//Bulksms sms api url builder
	global $plugin_config;
	$query_string = "submission/send_sms/2/2.0?username=" . $plugin_config['bulksms']['username'] . "&password=" . $plugin_config['bulksms']['password'] . "&msisdn=" . urlencode($sms_to) . "&msg_class=".$sms_class."&message=" . urlencode($sms_msg) . "&dca=" . $sms_dca . $set_sms_from;
	$url = $plugin_config['bulksms']['send_url'] . "/" . $query_string;
	
	if ($additional_param = $plugin_config['bulksms']['additional_param']) {
		$additional_param = "&" . $additional_param;
	} else {
		$additional_param = "routing_group=1&repliable=0";
	}
	$url .= $additional_param;
	$url = str_replace("&&", "&", $url);
	return $url;
}

function convert_to_unicode($unicode, $message){
	if($unicode == 1 ){
		if (function_exists('mb_convert_encoding')) {
			$message = mb_convert_encoding($message, "UCS-2BE", "auto");
			// $sms_msg = utf8ToUnicode($sms_msg);
		}
		$message = core_str2hex($message);
	}
	return $message;
}

function buklsms_multiple_sms($sms_to, $sms_class, $sms_msg,$sms_dca ,$set_sms_from, $unicode){
	// Bulksms provider does not support sms with a length > 160 (7bit) or > 70 (16bit)
	// we need to split sms according to sms length and send each one of then. 0
	// Based on http://stackoverflow.com/questions/6700556/how-to-split-up-a-message-longer-than-160-chars-into-multiple-messages-for-sendi
	global $plugin_config;
	
	$divider = ($unicode == 1)?70:160;
	$page_idicator_length = 8;
	
	if(strlen($sms_msg)>$divider){
		$messages = str_split($sms_msg, ($divider-$page_idicator_length));
		$how_many = count($messages);
		foreach ($messages as $index => $message) {
				$msg_number = ($index+1);				
				$sms = convert_to_unicode($unicode, "(".$msg_number."/".$how_many.") ".$message);
				$url = 	bulksms_sms_url($sms_to, $sms_class, $sms, $sms_dca, $set_sms_from);
				$fd = @implode('', file($url));
				if($msg_number == $how_many){
					// let's track the last part of our long sms
					return $fd;
				}
			}
	}	
	$sms_msg = convert_to_unicode($unicode, $sms_msg);	
	$url = 	bulksms_sms_url($sms_to, $sms_class, $sms_msg, $sms_dca, $set_sms_from);
	logger_print("url:" . $url, 3, "bulksms outgoing");
	$fd = @implode('', file($url));
	return $fd;
}

function bulksms_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	// Based on http://www.bulksms.com/int/docs/eapi/submission/send_sms/
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "bulksms_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	if ($plugin_config['bulksms']['module_sender']) {
		$sms_sender = $plugin_config['bulksms']['module_sender'];
	}
	
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$sms_from = $sms_sender;
	$sms_dca = "7bit";
	$sms_class = "2";
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	switch ($sms_type) {
		case "flash" :			
			$sms_class = "0";
			break;
		case "logo" :
		case "picture" :
		case "ringtone" :
		case "rtttl" :
			$sms_dca = "8bit";
			break;
		default :
			$sms_dca = "7bit";
			$sms_class = "2";
	}
	
	// Automatically setting the unicode flag if necessary
	if (!$unicode) {		
		$unicode = core_detect_unicode($sms_msg);
	}
	
	if ($unicode) {
		$unicode = 1;
		$sms_dca = "16bit";
	}
	
	// fixme anton - if sms_from is not set in gateway_number and global number, we cannot pass it to bulksms
	$set_sms_from = ($sms_from == $sms_sender ? '' : "&sender=" . urlencode($sms_from));
	
	// $query_string = "submission/send_sms/2/2.0?username=" . $plugin_config['bulksms']['username'] . "&password=" . $plugin_config['bulksms']['password'] . "&msisdn=" . urlencode($sms_to) . "&msg_class=$sms_class&message=" . urlencode($sms_msg) . "&dca=" . $sms_dca . $set_sms_from;
	// $url = $plugin_config['bulksms']['send_url'] . "/" . $query_string;
	
	// if ($additional_param = $plugin_config['bulksms']['additional_param']) {
	// 	$additional_param = "&" . $additional_param;
	// } else {
	// 	$additional_param = "routing_group=1&repliable=0";
	// }
	// $url .= $additional_param;
	// $url = str_replace("&&", "&", $url);
	
	// logger_print("url:" . $url, 3, "bulksms outgoing");
	// $fd = @implode('', file($url));

	$fd = buklsms_multiple_sms($sms_to, $sms_class, $sms_msg,$sms_dca ,$set_sms_from, $unicode);

	$ok = false;
	// failed
	$p_status = 2;
	if ($fd) {

		$response = explode("|", $fd);

		if ((count( $response ) == 3)) {

			$status_code = trim($response[0]);
			$apimsgid = trim($response[2]);
			bulksms_setsmsapimsgid($smslog_id, $apimsgid);

			if ($status_code == '1') {
				list($c_sms_credit, $c_sms_status) = bulksms_getsmsstatus($smslog_id);
				// pending
				$p_status = 0;
				if ($c_sms_status) {
					$p_status = $c_sms_status;
				}
			} else {
				// sent
				$p_status = 1;
			}
			logger_print("smslog_id:" . $smslog_id . " charge:" . $c_sms_credit . " sms_status:" . $p_status . " response:" . $response[0] . " " . $response[1]. " " . $response[2], 2, "bulksms outgoing");

		} else {
			// even when the response is not what we expected we still print it out for debug purposes
			$fd = str_replace("\n", " ", $fd);
			$fd = str_replace("\r", " ", $fd);
			logger_print("smslog_id:" . $smslog_id . " response:" . $fd, 2, "bulksms outgoing");
		}
		$ok = true;
	}
	dlr($smslog_id, $uid, $p_status);
	return $ok;
}

function bulksms_getsmsstatus($smslog_id) {
	// Based on http://www.bulksms.com/int/docs/eapi/status_reports/get_report/
	global $plugin_config;
	$c_sms_status = 0;
	$c_sms_credit = 0;
	$db_query = "SELECT apimsgid FROM " . _DB_PREF_ . "_gatewayBulksms_apidata WHERE smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($apimsgid = $db_row['apimsgid']) {
		//TODO: get charge
		$query_string = "status_reports/get_report/2/2.0?username=" . $plugin_config['bulksms']['username'] . "&password=" . $plugin_config['bulksms']['password'] . "&batch_id=$apimsgid&optional_fields=credits";
		
		$url = $plugin_config['bulksms']['send_url'] . "/" . $query_string;
		logger_print("smslog_id:" . $smslog_id . " apimsgid:" . $apimsgid . " url:" . $url, 3, "bulksms getsmsstatus");

		$fd = @implode('', file($url));
		if ($fd) {
			$lines = explode("\n", $fd);
			if(count($lines) >=4 ){

				$response = explode("|", trim($lines[2]));
				if(count($response) >=3 ){
					$status_code = trim($response[1]);
					$credit = 0;
					if($credit = trim($response[2])){
						$c_sms_credit = $credit;
					}						
					switch ($status_code) {
						case "0" :
							$c_sms_status = 0;
							break; // pending
						case "10" :						
						case "12" :
							$c_sms_status = 1;
							break; // sent
						case "11" :
							$c_sms_status = 3;
							break; // delivered
						default:
							$c_sms_status = 2;
							//failed							
					}
					
					logger_print("smslog_id:" . $smslog_id . " apimsgid:" . $apimsgid . " charge:" . $credit . " status:" . $status . " sms_status:" . $c_sms_status, 2, "bulksms getsmsstatus");
				}
			}else{
				logger_print("smslog_id:" . $smslog_id . " apimsgid:" . $apimsgid . " response:" . $fd, 2, "bulksms getsmsstatus");
			}
		}
	}
	return array(
		$c_sms_credit,
		$c_sms_status 
	);
}

function bulksms_setsmsapimsgid($smslog_id, $apimsgid) {
	if ($smslog_id && $apimsgid) {
		$db_query = "INSERT INTO " . _DB_PREF_ . "_gatewayBulksms_apidata (smslog_id,apimsgid) VALUES ('$smslog_id','$apimsgid')";
		$db_result = dba_query($db_query);
	}
}

function bulksms_hook_call($requests) {
	// please note that we must globalize these 2 variables
	global $core_config, $plugin_config;
	$called_from_hook_call = true;
	$access = $requests['access'];
	if ($access == 'callback') {
		$fn = $core_config['apps_path']['plug'] . '/gateway/bulksms/callback.php';
		logger_print("start load:" . $fn, 2, "bulksms call");
		include $fn;
		logger_print("end load callback", 2, "bulksms call");
	}
}
