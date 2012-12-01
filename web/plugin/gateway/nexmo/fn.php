<?php
defined('_SECURE_') or die('Forbidden');

// hook_sendsms
// called by main sms sender
// return true for success delivery
// $sms_sender	: sender mobile number
// $sms_footer	: sender sms footer or sms sender ID
// $sms_to	: destination sms number
// $sms_msg	: sms message tobe delivered
// $gpid	: group phonebook id (optional)
// $uid		: sender User ID
// $smslog_id	: sms ID
function nexmo_hook_sendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
	global $nexmo_param;
	$sms_sender = stripslashes($sms_sender);
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$ok = false;

	if ($sms_footer) {
		$sms_msg = $sms_msg.$sms_footer;
	}
	
	if ($sms_sender && $sms_to && $sms_msg) {

		$unicode = "";
		if ($unicode) {
			if (function_exists('mb_convert_encoding')) {
				// $sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
				$sms_msg = mb_convert_encoding($sms_msg, "UCS-2", "auto");
				$unicode = "&type=unicode"; // added at the of query string if unicode
			}
		}
		$query_string = "api_key=".$nexmo_param['api_key']."&api_secret=".$nexmo_param['api_secret']."&to=".urlencode($sms_to)."&from=".urlencode($sms_sender)."&text=".urlencode($sms_msg).$unicode."&status-report-req=1&client-ref=".$smslog_id;
		$url = $nexmo_param['url']."?".$query_string;

		logger_print($url, 3, "nexmo outgoing");
		$resp = json_decode(file_get_contents($url), true);
		if ($resp['message-count']) {
			$c_status = $resp['messages'][0]['status'];
			$c_message_id = $resp['messages'][0]['message-id'];
			$c_network = $resp['messages'][0]['network'];
			$c_error_text = $resp['messages'][0]['error-text'];
			logger_print("sent smslog_id:".$smslog_id." message_id:".$c_message_id." status:".$c_status." error:".$c_error_text, 2, "nexmo outgoing");
			$db_query = "
				INSERT INTO "._DB_PREF_."_gatewayNexmo (local_slid,remote_slid,status,network,error_text)
				VALUES ('$smslog_id','$c_message_id','$c_status','$c_network','$c_error_text')";
			$id = @dba_insert_id($db_query);
			if ($id && ($c_status == 0)) {
				$ok = true;
				$p_status = 1;
				setsmsdeliverystatus($smslog_id,$uid,$p_status);
			}
		} else {
			// even when the response is not what we expected we still print it out for debug purposes
			$resp = str_replace("\n", " ", $resp);
			$resp = str_replace("\r", " ", $resp);
			logger_print("failed smslog_id:".$smslog_id." resp:".$resp, 2, "nexmo outgoing");
		}
	}
	if (!$ok) {
		$p_status = 2;
		setsmsdeliverystatus($smslog_id,$uid,$p_status);
	}
	return $ok;
}

?>