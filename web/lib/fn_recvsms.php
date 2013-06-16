<?php
defined('_SECURE_') or die('Forbidden');

/*
 * Check available keyword or keyword that hasn't been added
 *
 * @param $keyword
 *    keyword
 * @return
 *    TRUE if available, FALSE if already exists or not available
 */
function checkavailablekeyword($keyword) {
	global $reserved_keywords, $core_config;
	$ok = true;
	$reserved = false;
	$keyword = trim(strtoupper($keyword));
	for ($i=0;$i<count($reserved_keywords);$i++) {
		if ($keyword == trim(strtoupper($reserved_keywords[$i]))) {
			$reserved = true;
		}
	}
	// if reserved returns not available, FALSE
	if ($reserved) {
		$ok = false;
	} else {
		for ($c=0;$c<count($core_config['featurelist']);$c++) {
			// checkavailablekeyword() on hooks will return TRUE as well if keyword is available
			// so we're looking for FALSE value
			if (x_hook($core_config['featurelist'][$c],'checkavailablekeyword',array($keyword)) === FALSE) {
				$ok = false;
				break;
			}
		}
	}
	return $ok;
}

function interceptincomingsms($sms_datetime,$sms_sender,$message,$sms_receiver="") {
	global $core_config;
	$ret = array();
	$ret_final = array();
	// feature list
	for ($c=0;$c<count($core_config['featurelist']);$c++) {
		$ret = x_hook($core_config['featurelist'][$c],'interceptincomingsms',array($sms_datetime,$sms_sender,$message,$sms_receiver));
		if ($ret['modified']) {
			$sms_datetime = ( $ret['param']['sms_datetime'] ? $ret['param']['sms_datetime'] : $sms_datetime );
			$sms_sender = ( $ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender );
			$message = ( $ret['param']['message'] ? $ret['param']['message'] : $message );
			$sms_receiver = ( $ret['param']['sms_receiver'] ? $ret['param']['sms_receiver'] : $sms_receiver );
			$ret_final['modified'] = $ret['modified'];
			$ret_final['cancel'] = $ret['cancel'];
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
		}
		if ($ret['hooked']) { $ret_final['hooked'] = $ret['hooked']; };
	}
	// tools list
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		$ret = x_hook($core_config['toolslist'][$c],'interceptincomingsms',array($sms_datetime,$sms_sender,$message,$sms_receiver));
		if ($ret['modified']) {
			$sms_datetime = ( $ret['param']['sms_datetime'] ? $ret['param']['sms_datetime'] : $sms_datetime );
			$sms_sender = ( $ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender );
			$message = ( $ret['param']['message'] ? $ret['param']['message'] : $message );
			$sms_receiver = ( $ret['param']['sms_receiver'] ? $ret['param']['sms_receiver'] : $sms_receiver );
			$ret_final['modified'] = $ret['modified'];
			$ret_final['cancel'] = $ret['cancel'];
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
		}
		if ($ret['hooked']) { $ret_final['hooked'] = $ret['hooked']; };
	}
	return $ret_final;
}

function setsmsincomingaction($sms_datetime,$sms_sender,$message,$sms_receiver="") {
	global $core_config;

	$gw = gateway_get();

	// make sure sms_datetime is in supported format and in GMT+0
	$sms_datetime = core_adjust_datetime($sms_datetime);

	// incoming sms will be handled by plugin/tools/* first
	$ret_intercept = interceptincomingsms($sms_datetime,$sms_sender,$message,$sms_receiver);
	if ($ret_intercept['modified']) {
		$sms_datetime = ( $ret_intercept['param']['sms_datetime'] ? $ret_intercept['param']['sms_datetime'] : $sms_datetime );
		$sms_sender = ( $ret_intercept['param']['sms_sender'] ? $ret_intercept['param']['sms_sender'] : $sms_sender );
		$message = ( $ret_intercept['param']['message'] ? $ret_intercept['param']['message'] : $message );
		$sms_receiver = ( $ret_intercept['param']['sms_receiver'] ? $ret_intercept['param']['sms_receiver'] : $sms_receiver );
	}

	// if hooked function returns cancel=true then stop the processing incoming sms, return false
	if ($ret_intercept['cancel']) {
		logger_print("cancelled datetime:".$sms_datetime." sender:".$sms_sender." receiver:".$sms_receiver." message:".$message, 3, "setsmsincomingaction");
		return false;
	}

	$c_uid = 0;
	$c_feature = "";
	$ok = false;
	$array_target_keyword = explode(" ",$message);
	$target_keyword = strtoupper(trim($array_target_keyword[0]));
	$raw_message = $message;
	$message = $array_target_keyword[1];
	for ($i=2;$i<count($array_target_keyword);$i++) {
		$message .= " ".$array_target_keyword[$i];
	}
	switch ($target_keyword) {
		case "BC":
			$c_uid = mobile2uid($sms_sender);
			$c_username = uid2username($c_uid);
			$c_feature = 'core';
			$array_target_group = explode(" ",$message);
			$target_group = strtoupper(trim($array_target_group[0]));
			$c_gpid = phonebook_groupcode2id($c_uid, $target_group);
			$message = $array_target_group[1];
			for ($i=2;$i<count($array_target_group);$i++) {
				$message .= " ".$array_target_group[$i];
			}
			logger_print("username:".$c_username." gpid:".$c_gpid." sender:".$sms_sender." receiver:".$sms_receiver." message:".$message." raw:".$raw_message, 3, "setsmsincomingaction bc");
			list($ok,$to,$smslog_id,$queue) = sendsms_bc($c_username,$c_gpid,$message);
			$ok = true;
			break;
		default:
			for ($c=0;$c<count($core_config['featurelist']);$c++) {
				$c_feature = $core_config['featurelist'][$c];
				$ret = x_hook($c_feature,'setsmsincomingaction',array($sms_datetime,$sms_sender,$target_keyword,$message,$sms_receiver,$raw_message));
				if ($ok = $ret['status']) {
					$c_uid = $ret['uid'];
					logger_print("feature:".$c_feature." datetime:".$sms_datetime." sender:".$sms_sender." receiver:".$sms_receiver." keyword:".$target_keyword." message:".$message." raw:".$raw_message, 3, "setsmsincomingaction");
					break;
				}
			}
	}
	$c_status = ( $ok ? 1 : 0 );
	if ($c_status == 0) {
		$c_feature = '';
		$target_keyword = '';
		$message = $raw_message;
		// from interceptincomingsms(), force status as 'handled'
		if ($ret_intercept['hooked']) {
			$c_status = 1;
			logger_print("intercepted datetime:".$sms_datetime." sender:".$sms_sender." receiver:".$sms_receiver." message:".$message, 3, "setsmsincomingaction");
		} else {
			logger_print("unhandled datetime:".$sms_datetime." sender:".$sms_sender." receiver:".$sms_receiver." message:".$message, 3, "setsmsincomingaction");
		}
	}

	// fixme anton - all incoming messages set to user with uid=1 if no one owns it
	$c_uid = ( $c_uid ? $c_uid : 1 );

	$db_query = "
		INSERT INTO "._DB_PREF_."_tblSMSIncoming 
		(in_uid,in_feature,in_gateway,in_sender,in_receiver,in_keyword,in_message,in_datetime,in_status)
		VALUES
		('$c_uid','$c_feature','$gw','$sms_sender','$sms_receiver','$target_keyword','$message','$sms_datetime','$c_status')";
	$db_result = dba_query($db_query);

	return $ok;
}

function interceptsmstoinbox($sms_datetime,$sms_sender,$target_user,$message,$sms_receiver="") {
	global $core_config;
	$ret = array();
	$ret_final = array();
	// feature list
	for ($c=0;$c<count($core_config['featurelist']);$c++) {
		if ($ret['modified']) {
			$ret_final['modified'] = $ret['modified'];
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['target_user'] = $ret['param']['target_user'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
			$sms_datetime = ( $ret['param']['sms_datetime'] ? $ret['param']['sms_datetime'] : $sms_datetime );
			$sms_sender = ( $ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender );
			$target_user = ( $ret['param']['target_user'] ? $ret['param']['target_user'] : $target_user );
			$message = ( $ret['param']['message'] ? $ret['param']['message'] : $message );
			$sms_receiver = ( $ret['param']['sms_receiver'] ? $ret['param']['sms_receiver'] : $sms_receiver );
		}
		$ret = x_hook($core_config['featurelist'][$c],'interceptsmstoinbox',array($sms_datetime,$sms_sender,$target_user,$message,$sms_receiver));
	}
	// tools list
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($ret['modified']) {
			$ret_final['modified'] = $ret['modified'];
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['target_user'] = $ret['param']['target_user'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
			$sms_datetime = ( $ret['param']['sms_datetime'] ? $ret['param']['sms_datetime'] : $sms_datetime );
			$sms_sender = ( $ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender );
			$target_user = ( $ret['param']['target_user'] ? $ret['param']['target_user'] : $target_user );
			$message = ( $ret['param']['message'] ? $ret['param']['message'] : $message );
			$sms_receiver = ( $ret['param']['sms_receiver'] ? $ret['param']['sms_receiver'] : $sms_receiver );
		}
		$ret = x_hook($core_config['toolslist'][$c],'interceptsmstoinbox',array($sms_datetime,$sms_sender,$target_user,$message,$sms_receiver));
	}
	return $ret_final;
}

function insertsmstoinbox($sms_datetime,$sms_sender,$target_user,$message,$sms_receiver="") {
	global $core_config,$web_title,$email_service,$email_footer;

	// sms to inbox will be handled by plugin/tools/* first
	$ret_intercept = interceptsmstoinbox($sms_datetime,$sms_sender,$target_user,$message,$sms_receiver);
	if ($ret_intercept['param_modified']) {
		$sms_datetime = ( $ret_intercept['param']['sms_datetime'] ? $ret_intercept['param']['sms_datetime'] : $sms_datetime );
		$sms_sender = ( $ret_intercept['param']['sms_sender'] ? $ret_intercept['param']['sms_sender'] : $sms_sender );
		$target_user = ( $ret_intercept['param']['target_user'] ? $ret_intercept['param']['target_user'] : $target_user );
		$message = ( $ret_intercept['param']['message'] ? $ret_intercept['param']['message'] : $message );
		$sms_receiver = ( $ret_intercept['param']['sms_receiver'] ? $ret_intercept['param']['sms_receiver'] : $sms_receiver );
	}

	$ok = false;
	if ($sms_sender && $target_user && $message) {
		$user = user_getdatabyusername($target_user);
		if ($uid = $user['uid']) {
			// get name from target_user's phonebook
			$c_name = phonebook_number2name($sms_sender, $target_user);
			$sender = $c_name ? $c_name.' <'.$sms_sender.'>' : $sms_sender;

			// forward to Inbox
			if ($fwd_to_inbox = $user['fwd_to_inbox']) {
				$db_query = "
					INSERT INTO "._DB_PREF_."_tblUserInbox
					(in_sender,in_receiver,in_uid,in_msg,in_datetime) 
					VALUES ('$sms_sender','$sms_receiver','$uid','$message','$sms_datetime')
				";
				logger_print("saving sender:".$sms_sender." receiver:".$sms_receiver." target:".$target_user, 2, "insertsmstoinbox");
				if ($cek_ok = @dba_insert_id($db_query)) {
					logger_print("saved sender:".$sms_sender." receiver:".$sms_receiver." target:".$target_user, 2, "insertsmstoinbox");
				}
			}
			// forward to email
			if ($fwd_to_email = $user['fwd_to_email']) {
				if ($email = $user['email']) {
					// make sure sms_datetime is in supported format and in user's timezone
					$sms_datetime = core_display_datetime($sms_datetime);
					$subject = _('Forward from')." ".$sms_sender;
					$body = _('Forward Private WebSMS')." ($web_title)\n\n";
					$body .= _('Date time').": $sms_datetime\n";
					$body .= _('Sender').": $sender\n";
					$body .= _('Receiver').": $sms_receiver\n\n";
					$body .= _('Message').":\n$message\n\n";
					$body .= $email_footer."\n\n";
					$body = stripslashes($body);
					logger_print("send email from:".$email_service." to:".$email." message:".$message, 3, "insertsmstoinbox");
					sendmail($email_service,$email,$subject,$body);
					logger_print("sent email from:".$email_service." to:".$email." message:".$message, 3, "insertsmstoinbox");
				}
				$ok = true;
			}
			// forward to mobile
			if ($fwd_to_mobile = $user['fwd_to_mobile']) {
				if ($mobile = $user['mobile']) {
					$unicode = core_detect_unicode($message);
					$message = $sender.' '.$message;
					logger_print("send to mobile:".$mobile." from:".$sms_sender." user:".$target_user." message:".$message, 3, "insertsmstoinbox");
					list($ok, $to, $smslog_id, $queue) = sendsms($target_user, $mobile, $message, 'text', $unicode);
					if ($ok[0]==1) {
                                                logger_print("sent to mobile:".$mobile." from:".$sms_sender." user:".$target_user, 2, "insertsmstoinbox");
					}
				}
			}
		}
	}
	return $ok;
}

function setsmsdeliverystatus($smslog_id,$uid,$p_status) {
	global $core_config;
	// $p_status = 0 --> pending
	// $p_status = 1 --> sent
	// $p_status = 2 --> failed
	// $p_status = 3 --> delivered
	//logger_print("smslog_id:".$smslog_id." uid:".$uid." p_status:".$p_status, 2, "setsmsdeliverystatus");
	$ok = false;
	$db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".mktime()."',p_update='".core_get_datetime()."',p_status='$p_status' WHERE smslog_id='$smslog_id' AND uid='$uid'";
	if ($aff_id = @dba_affected_rows($db_query)) {
		//logger_print("saved smslog_id:".$smslog_id, 2, "setsmsdeliverystatus");
		$ok = true;
		if ($p_status > 0) {
			for ($c=0;$c<count($core_config['toolslist']);$c++) {
				x_hook($core_config['toolslist'][$c],'setsmsdeliverystatus',array($smslog_id,$uid,$p_status));
			}
			for ($c=0;$c<count($core_config['featurelist']);$c++) {
				x_hook($core_config['featurelist'][$c],'setsmsdeliverystatus',array($smslog_id,$uid,$p_status));
			}
			$gw = gateway_get();
			x_hook($gw,'setsmsdeliverystatus',array($smslog_id,$uid,$p_status));
		}
	}
	return $ok;
}

?>