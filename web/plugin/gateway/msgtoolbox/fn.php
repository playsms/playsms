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
function msgtoolbox_hook_sendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid='',$gpid=0,$smslog_id=0,$sms_type='text',$unicode=0) {
	// global $msgtoolbox_param;   // global all variables needed, eg: varibles from config.php
	// ...
	// ...
	// return true or false
	// return $ok;
	global $msgtoolbox_param;
	$sms_sender = stripslashes($sms_sender);
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$ok = false;

	if ($sms_footer) {
		$sms_msg = $sms_msg.$sms_footer;
	}
	
	if ($sms_to && $sms_msg) {

		if ($unicode) {
			if (function_exists('mb_convert_encoding')) {
				// $sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
				$sms_msg = mb_convert_encoding($sms_msg, "UCS-2", "auto");
				$unicode = "&coding=unicode"; // added at the of query string if unicode
			}
		}
		// fixme anton - from playSMS v0.9.5.1 references to input.php replaced with index.php?app=webservices
		// I should add autodetect, if its below v0.9.5.1 should use input.php
		$query_string = "username=".$msgtoolbox_param['username']."&password=".$msgtoolbox_param['password']."&to=".urlencode($sms_to)."&from=".urlencode($sms_sender)."&message=".urlencode($sms_msg).$unicode."&route=".$msgtoolbox_param['route'];
		$url = $msgtoolbox_param['url']."?".$query_string;

		/* not used
		if ($additional_param = $msgtoolbox_param['additional_param']) {
			$additional_param = "&".$additional_param;
		}
		$url .= $additional_param;
		$url = str_replace("&&", "&", $url);
		*/

		logger_print($url, 3, "msgtoolbox outgoing");
		$fd = @implode ('', file ($url));
		if ($fd) {
			$response = split (",", $fd);
			if (trim($response[0]) == "1") {
				$remote_slid = trim($response[1]);
				if ($remote_slid) {
					// this is for callback, if callback not used then the status would be sent or failed only
					// local_slid is local SMS log id (local smslog_id)
					// remote_slid is remote SMS log id (in API doc its referred to smsid or messageid)			
                                         // status=10 delivered to gateway
					$db_query = "
						INSERT INTO "._DB_PREF_."_gatewayMsgtoolbox (local_slid,remote_slid,status)
						VALUES ('$smslog_id','$remote_slid','10')
					    ";
					$id = @dba_insert_id($db_query);
					if ($id) {
						$ok = true;
						$p_status = 1; // sms sent
						setsmsdeliverystatus($smslog_id,$uid,$p_status);
					}
				}	
			}
			logger_print("sent smslog_id:".$smslog_id." response:".$fd, 2, "msgtoolbox outgoing");
		} else {
			// even when the response is not what we expected we still print it out for debug purposes
			$fd = str_replace("\n", " ", $fd);
			$fd = str_replace("\r", " ", $fd);
			logger_print("failed smslog_id:".$smslog_id." response:".$fd, 2, "msgtoolbox outgoing");
		}
	}
	if (!$ok) {
		$p_status = 2;
		setsmsdeliverystatus($smslog_id,$uid,$p_status);
	}
	return $ok;
}

?>