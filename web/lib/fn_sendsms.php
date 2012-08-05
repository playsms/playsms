<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

function sendsms_getvalidnumber($number) {
	$number_arr = explode(" ", $number);
	$number = preg_replace("/[^0-9\+]/", "", $number_arr[0]);
	if (strlen($number) > 20) {
		$number = substr($number, 0, 20);
	}
	return $number;
}

function sendsms_manipulate_prefix($number, $user) {
	if (is_array($user)) {
		if ($user['replace_zero']) {
			$number = preg_replace('/^0/', $user['replace_zero'], $number);
		}
		if ($user['plus_sign_remove']) {
			$number = preg_replace('/^\+/', '', $number);
		}
		if ($user['plus_sign_add']) {
			$number = '+'.$number;
		}
	}
	return $number;
}

function interceptsendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid,$gpid=0,$sms_type='text',$unicode=0) {
	global $core_config;
	$ret = array();
	$ret_final = array();
	// feature list
	for ($c=0;$c<count($core_config['featurelist']);$c++) {
		$ret = x_hook($core_config['featurelist'][$c],'interceptsendsms',array($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid,$gpid,$sms_type,$unicode));
		if ($ret['modified']) {
			$sms_sender = ( $ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender );
			$sms_footer = ( $ret['param']['sms_footer'] ? $ret['param']['sms_footer'] : $sms_footer );
			$sms_to = ( $ret['param']['sms_to'] ? $ret['param']['sms_to'] : $sms_to );
			$sms_msg = ( $ret['param']['sms_msg'] ? $ret['param']['sms_msg'] : $sms_msg );
			$uid = ( $ret['param']['uid'] ? $ret['param']['uid'] : $uid );
			$gpid = ( $ret['param']['gpid'] ? $ret['param']['gpid'] : $gpid );
			$sms_type = ( $ret['param']['sms_type'] ? $ret['param']['sms_type'] : $sms_type );
			$unicode = ( $ret['param']['unicode'] ? $ret['param']['unicode'] : $unicode );
			$ret_final['modified'] = $ret['modified'];
			$ret_final['cancel'] = $ret['cancel'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['sms_footer'] = $ret['param']['sms_footer'];
			$ret_final['param']['sms_to'] = $ret['param']['sms_to'];
			$ret_final['param']['sms_msg'] = $ret['param']['sms_msg'];
			$ret_final['param']['uid'] = $ret['param']['uid'];
			$ret_final['param']['gpid'] = $ret['param']['gpid'];
			$ret_final['param']['sms_type'] = $ret['param']['sms_type'];
			$ret_final['param']['unicode'] = $ret['param']['unicode'];
		}
	}
	// tools list
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		$ret = x_hook($core_config['toolslist'][$c],'interceptsendsms',array($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid,$gpid,$sms_type,$unicode));
		if ($ret['modified']) {
			$sms_sender = ( $ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender );
			$sms_footer = ( $ret['param']['sms_footer'] ? $ret['param']['sms_footer'] : $sms_footer );
			$sms_to = ( $ret['param']['sms_to'] ? $ret['param']['sms_to'] : $sms_to );
			$sms_msg = ( $ret['param']['sms_msg'] ? $ret['param']['sms_msg'] : $sms_msg );
			$uid = ( $ret['param']['uid'] ? $ret['param']['uid'] : $uid );
			$gpid = ( $ret['param']['gpid'] ? $ret['param']['gpid'] : $gpid );
			$sms_type = ( $ret['param']['sms_type'] ? $ret['param']['sms_type'] : $sms_type );
			$unicode = ( $ret['param']['unicode'] ? $ret['param']['unicode'] : $unicode );
			$ret_final['modified'] = $ret['modified'];
			$ret_final['cancel'] = $ret['cancel'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['sms_footer'] = $ret['param']['sms_footer'];
			$ret_final['param']['sms_to'] = $ret['param']['sms_to'];
			$ret_final['param']['sms_msg'] = $ret['param']['sms_msg'];
			$ret_final['param']['uid'] = $ret['param']['uid'];
			$ret_final['param']['gpid'] = $ret['param']['gpid'];
			$ret_final['param']['sms_type'] = $ret['param']['sms_type'];
			$ret_final['param']['unicode'] = $ret['param']['unicode'];
		}
	}
	return $ret_final;
}

function sendsms_queue_create($sms_sender,$sms_footer,$sms_msg,$uid,$sms_type='text',$unicode=0) {
	global $datetime_now;
	$ret = FALSE;
	$queue_code = md5(mktime().$uid.$sms_msg);
	$db_query = "INSERT INTO "._DB_PREF_."_tblSMSOutgoing_queue ";
	$db_query .= "(queue_code,datetime_entry,datetime_scheduled,uid,sender_id,footer,message,sms_type,unicode) ";
	$db_query .= "VALUES ('$queue_code','$datetime_now','$datetime_now','$uid','$sms_sender','$sms_footer','$sms_msg','$sms_type','$unicode')";
	logger_print("saving:$queue_code,$datetime_now,$uid,$sms_sender,$sms_footer,$sms_type,$unicode", 3, "sendsms_queue_create");
	if ($id = @dba_insert_id($db_query)) {
		logger_print("id:".$id." queue_code:".$queue_code." saved", 3, "sendsms_queue_create");
		$ret = $queue_code;
	}
	return $ret;
}

function sendsms_queue_push($queue_code,$sms_to) {
	$ok = false;
	$db_query = "SELECT id FROM "._DB_PREF_."_tblSMSOutgoing_queue WHERE queue_code='$queue_code'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$queue_id = $db_row['id'];
	if ($queue_id) {
		$db_query = "INSERT INTO "._DB_PREF_."_tblSMSOutgoing_queue_dst (queue_id,dst) VALUES ('$queue_id','$sms_to')";
		logger_print("saving:$queue_code,$sms_to", 3, "sendsms_queue_push");
		if ($id = @dba_insert_id($db_query)) {
			logger_print("id:".$id." queue_code:".$queue_code." dst:".$sms_to." saved", 3, "sendsms_queue_push");
			$ok = true;
		}
	}
	return $ok;
}

function sendsmsd() {
	global $datetime_now;
	$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing_queue WHERE flag='0'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$c_queue_id = $db_row['id'];
		$c_sender_id = $db_row['sender_id'];
		$c_footer = $db_row['footer'];
		$c_message = $db_row['message'];
		$c_uid = $db_row['uid'];
		$c_gpid = 0;
		$c_sms_type = $db_row['sms_type'];
		$c_unicode = $db_row['unicode'];
		$db_query2 = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing_queue_dst WHERE queue_id='$c_queue_id' AND flag='0'";
		$db_result2 = dba_query($db_query2);
		while ($db_row2 = dba_fetch_array($db_result2)) {
			$c_id = $db_row2['id'];
			$c_dst = $db_row2['dst'];
			$c_flag = 0;
			$ret = sendsms($c_sender_id,$c_footer,$c_dst,$c_message,$c_uid,$c_gpid,$c_sms_type,$c_unicode);
			if ($ret['status']) {
				$c_flag = 1;
			}
			$db_query3 = "UPDATE "._DB_PREF_."_tblSMSOutgoing_queue_dst SET flag='$c_flag' WHERE id='$c_id'";
			$db_result3 = dba_query($db_query3);
		}
		$c_flag = 0;
		$db_query4 = "SELECT count(*) AS rows FROM "._DB_PREF_."_tblSMSOutgoing_queue_dst WHERE queue_id='$c_queue_id' AND flag='0'";
		$db_result4 = dba_query($db_query4);
		$db_row4 = dba_fetch_array($db_result4);
		$unprocessed_rows = $db_row4['rows'];
		if ($unprocessed_rows === 0) {
			$c_flag = 1;
		}
		$db_query5 = "UPDATE "._DB_PREF_."_tblSMSOutgoing_queue SET flag='$c_flag', datetime_update='$datetime_now' WHERE id='$c_queue_id'";
		$db_result5 = dba_query($db_query5);
	}
}

function sendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid,$gpid=0,$sms_type='text',$unicode=0) {
	global $datetime_now, $core_config, $gateway_module;

	$user = user_getdatabyuid($uid);
	$username = $user['username'];

	$sms_to = sendsms_getvalidnumber($sms_to);
	$sms_to = sendsms_manipulate_prefix($sms_to, $user);

	// make sure sms_datetime is in supported format and in GMT+0
	// timezone used for outgoing message is not module timezone, but default timezone
	// module gateway may have set already to +0000 (such kannel and clickatell)
	$sms_datetime = core_adjust_datetime($core_config['datetime']['now'], $core_config['main']['cfg_datetime_timezone']);

	// sent sms will be handled by plugin/tools/* first
	$ret_intercept = interceptsendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid,$gpid,$sms_type,$unicode);
	if ($ret_intercept['modified']) {
		$sms_sender = ( $ret_intercept['param']['sms_sender'] ? $ret_intercept['param']['sms_sender'] : $sms_sender );
		$sms_footer = ( $ret_intercept['param']['sms_footer'] ? $ret_intercept['param']['sms_footer'] : $sms_footer );
		$sms_to = ( $ret_intercept['param']['sms_to'] ? $ret_intercept['param']['sms_to'] : $sms_to );
		$sms_msg = ( $ret_intercept['param']['sms_msg'] ? $ret_intercept['param']['sms_msg'] : $sms_msg );
		$uid = ( $ret_intercept['param']['uid'] ? $ret_intercept['param']['uid'] : $uid );
		$gpid = ( $ret_intercept['param']['gpid'] ? $ret_intercept['param']['gpid'] : $gpid );
		$sms_type = ( $ret_intercept['param']['sms_type'] ? $ret_intercept['param']['sms_type'] : $sms_type );
		$unicode = ( $ret_intercept['param']['unicode'] ? $ret_intercept['param']['unicode'] : $unicode );
	}

	// if hooked function returns cancel=true then stop the sending, return false
	if ($ret_intercept['cancel']) {
		logger_print("cancelled:$uid,$gpid,$gateway_module,$sms_sender,$sms_to,$sms_type,$unicode", 3, "sendsms");
		$ret['status'] = false;
		return $ret;
	}

	// fixme anton - mobile number can be anything, screened by gateway
	// $sms_sender = sendsms_getvalidnumber($sms_sender);

	$ok = false;
	logger_print("start", 3, "sendsms");
	if (rate_cansend($username, $sms_to)) {
		// fixme anton - its a total mess ! need another DBA - we dont need this anymore
		//$sms_footer = addslashes(trim($sms_footer));
		//$sms_msg = addslashes($sms_msg);
		// we save all info first and then process with gateway module
		// the thing about this is that message saved may not be the same since gateway may not be able to process
		// message with that length or certain characters in the message are not supported by the gateway
		$db_query = "
			INSERT INTO "._DB_PREF_."_tblSMSOutgoing 
			(uid,p_gpid,p_gateway,p_src,p_dst,p_footer,p_msg,p_datetime,p_sms_type,unicode) 
			VALUES ('$uid','$gpid','$gateway_module','$sms_sender','$sms_to','$sms_footer','$sms_msg','$sms_datetime','$sms_type','$unicode')
		";
		logger_print("saving:$uid,$gpid,$gateway_module,$sms_sender,$sms_to,$sms_type,$unicode", 3, "sendsms");
		// continue to gateway only when save to db is true
		if ($smslog_id = @dba_insert_id($db_query)) {
			logger_print("smslog_id:".$smslog_id." saved", 3, "sendsms");
			// fixme anton - another mess with slashes! also trim $sms_footer and prefix it with a space
			$sms_footer = ' '.stripslashes(trim($sms_footer));
			$sms_msg = stripslashes($sms_msg);
			if (x_hook($gateway_module, 'sendsms', array($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid,$gpid,$smslog_id,$sms_type,$unicode))) {
				// fixme anton - deduct user's credit as soon as gateway returns true
				rate_deduct($smslog_id);
				$ok = true;
			}
		}
	}
	logger_print("end", 3, "sendsms");

	$ret['status'] = $ok;
	$ret['smslog_id'] = $smslog_id;
	return $ret;
}

function sendsms_pv($username,$sms_to,$message,$sms_type='text',$unicode=0) {
	global $apps_path, $core_config;
	global $datetime_now, $gateway_module;
	$uid = username2uid($username);
	$sms_sender = sendsms_get_sender($username);
	$max_length = ( $unicode ?  $core_config['smsmaxlength_unicode'] : $core_config['smsmaxlength'] );
	if ($sms_footer = username2footer($username)) {
		$max_length = $max_length - strlen($sms_footer) - 1;
	}
	if (strlen($message)>$max_length) {
		$message = substr ($message,0,$max_length);
	}
	$sms_msg = $message;

	// \r and \n is ok - http://smstools3.kekekasvi.com/topic.php?id=328
	//$sms_msg = str_replace("\r","",$sms_msg);
	//$sms_msg = str_replace("\n","",$sms_msg);
        
	//$sms_msg = str_replace("\"","'",$sms_msg);

	if (is_array($sms_to)) {
		$array_sms_to = $sms_to;
	} else {
		$array_sms_to[0] = $sms_to;
	}
	for ($i=0;$i<count($array_sms_to);$i++) {
		$c_sms_to = str_replace("\'","",$array_sms_to[$i]);
		$c_sms_to = str_replace("\"","",$c_sms_to);
		$to[$i] = $c_sms_to;
		$ok[$i] = false;
		if ($ret = sendsms($sms_sender,$sms_footer,$c_sms_to,$sms_msg,$uid,0,$sms_type,$unicode)) {
			$ok[$i] = $ret['status'];
			$smslog_id[$i] = $ret['smslog_id'];
		}
	}
	return array($ok,$to,$smslog_id);
}

function sendsms_bc($username,$gpid,$message,$sms_type='text',$unicode=0) {
	global $apps_path, $core_config;
	global $datetime_now, $gateway_module;
	$uid = username2uid($username);
	$sms_sender = sendsms_get_sender($username);
	$max_length = ( $unicode ?  $core_config['smsmaxlength_unicode'] : $core_config['smsmaxlength'] );
	if ($sms_footer = username2footer($username)) {
		$max_length = $max_length - strlen($sms_footer) - 1;
	}
	if (strlen($message)>$max_length) {
		$message = substr ($message,0,$max_length);
	}
	$sms_msg = $message;

	// \r and \n is ok - http://smstools3.kekekasvi.com/topic.php?id=328
	//$sms_msg = str_replace("\r","",$sms_msg);
	//$sms_msg = str_replace("\n","",$sms_msg);
	$sms_msg = str_replace("\"","'",$sms_msg);

	// destination group should be an array, if single then make it array of 1 member
	if (is_array($gpid)) {
		$array_gpid = $gpid;
	} else {
		$array_gpid[0] = $gpid;
	}

	// create a queue
	$queue_code = sendsms_queue_create($sms_sender,$sms_footer,$sms_msg,$uid,$sms_type,$unicode);
	if (! $queue_code) {
		// when unable to create a queue then immediately returns FALSE, no point to continue
		return FALSE;
	}

	$j=0;
	for ($i=0;$i<count($array_gpid);$i++) {
		$c_gpid = strtoupper($array_gpid[$i]);
		$rows = phonebook_getdatabyid($c_gpid);
		foreach ($rows as $key => $db_row) {
			$p_num = $db_row['p_num'];
			$sms_to = $p_num;
			$sms_to = str_replace("\'","",$sms_to);
			$sms_to = str_replace("\"","",$sms_to);
			$ok[$j] = 0;
			$to[$j] = $sms_to;
			$queue[$j] = $queue_code;

			// fill the queue with destination numbers
			if ($ret = sendsms_queue_push($queue_code,$sms_to)) {
				$ok[$j] = $ret['status'];
			}

			$j++;
		}
	}
	return array($ok,$to,$queue);
}

function sendsms_get_sender($username) {
	global $core_config;
	$gateway_module = $core_config['main']['cfg_gateway_module'];
	$gateway_number = $core_config['main']['cfg_gateway_number'];
	if ($gateway_module) {
		if ($core_config['plugin'][$gateway_module]['global_sender']) {
			$sms_sender = $core_config['plugin'][$gateway_module]['global_sender'];
		} else if ($gateway_number) {
			$sms_sender = $gateway_number;
		} else {
			$sms_sender = username2sender($username);
		}
	}
	$sms_sender = str_replace("\'","",$sms_sender);
	$sms_sender = str_replace("\"","",$sms_sender);
	return $sms_sender;
}

?>