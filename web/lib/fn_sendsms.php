<?php
defined('_SECURE_') or die('Forbidden');

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

function sendsms_queue_create($sms_sender,$sms_footer,$sms_msg,$uid,$gpid=0,$sms_type='text',$unicode=0) {
	global $core_config;
	$ret = FALSE;
	$queue_code = md5(mktime().$uid.$sms_msg);
	logger_print("saving:$queue_code,".$core_config['datetime']['now'].",$uid,$gpid,$sms_sender,$sms_footer,$sms_type,$unicode message:".$sms_msg, 3, "sendsms_queue_create");
	$db_query = "INSERT INTO "._DB_PREF_."_tblSMSOutgoing_queue ";
	$db_query .= "(queue_code,datetime_entry,datetime_scheduled,uid,gpid,sender_id,footer,message,sms_type,unicode) ";
	$db_query .= "VALUES ('$queue_code','".$core_config['datetime']['now']."','".$core_config['datetime']['now']."','$uid','$gpid','$sms_sender','$sms_footer','$sms_msg','$sms_type','$unicode')";
	if ($id = @dba_insert_id($db_query)) {
		logger_print("id:".$id." queue_code:".$queue_code." saved", 2, "sendsms_queue_create");
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
		logger_print("saving:$queue_code,$sms_to", 2, "sendsms_queue_push");
		if ($id = @dba_insert_id($db_query)) {
			logger_print("id:".$id." queue_code:".$queue_code." dst:".$sms_to." saved", 2, "sendsms_queue_push");
			$ok = true;
		}
	}
	return $ok;
}

function sendsmsd($single_queue='') {
	global $core_config;
	if ($single_queue) {
		$queue_sql = "AND queue_code='".$single_queue."'";
		logger_print("single queue queue_code:".$single_queue, 2, "sendsmsd");
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing_queue WHERE flag='0' ".$queue_sql;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$c_queue_id = $db_row['id'];
		$c_queue_code = $db_row['queue_code'];
		$c_sender_id = addslashes(trim($db_row['sender_id']));
		$c_footer = addslashes(trim($db_row['footer']));
		$c_message = addslashes(trim($db_row['message']));
		$c_uid = $db_row['uid'];
		$c_gpid = $db_row['gpid'];
		$c_sms_type = $db_row['sms_type'];
		$c_unicode = $db_row['unicode'];
		logger_print("start processing queue_code:".$c_queue_code." uid:".$c_uid." gpid:".$c_gpid." sender_id:".$c_sender_id, 2, "sendsmsd");
		$db_query2 = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing_queue_dst WHERE queue_id='$c_queue_id' AND flag='0'";
		$db_result2 = dba_query($db_query2);
		while ($db_row2 = dba_fetch_array($db_result2)) {
			$c_id = $db_row2['id'];
			$c_dst = $db_row2['dst'];
			$c_smslog_id = 0;
			$c_flag = 2;
			$c_ok = false;
			logger_print("sending queue_code:".$c_queue_code." to:".$c_dst, 2, "sendsmsd");
			$ret = sendsms($c_sender_id,$c_footer,$c_dst,$c_message,$c_uid,$c_gpid,$c_sms_type,$c_unicode);
			if ($ret['status'] && $ret['smslog_id']) {
				$c_ok = true;
				$c_smslog_id = $ret['smslog_id'];
				$c_flag = 1;
			}
			logger_print("result queue_code:".$c_queue_code." to:".$c_dst." flag:".$c_flag." smslog_id:".$c_smslog_id, 2, "sendsmsd");
			$db_query3 = "UPDATE "._DB_PREF_."_tblSMSOutgoing_queue_dst SET smslog_id='$c_smslog_id',flag='$c_flag' WHERE id='$c_id'";
			$db_result3 = dba_query($db_query3);
			$ok[] = $c_ok;
			$to[] = $c_dst;
			$smslog_id[] = $c_smslog_id;
			$queue[] = $c_queue_code;
		}
		$db_query5 = "UPDATE "._DB_PREF_."_tblSMSOutgoing_queue SET flag='1', datetime_update='".$core_config['datetime']['now']."' WHERE id='$c_queue_id'";
		if ($db_result5 = dba_affected_rows($db_query5)) {
			logger_print("finish processing queue_code:".$c_queue_code." uid:".$c_uid." sender_id:".$c_sender_id, 2, "sendsmsd");
		} else {
			logger_print("fail to finalize process queue_code:".$c_queue_code." uid:".$c_uid." sender_id:".$c_sender_id, 2, "sendsmsd");
		}
	}
	return array($ok, $to, $smslog_id, $queue);
}

function sendsms($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid,$gpid=0,$sms_type='text',$unicode=0) {
	global $core_config, $gateway_module;

	$user = $core_config['user'];
	if ($uid && ($user['uid'] != $uid)) {
		$user = user_getdatabyuid($uid);
	}

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
		logger_print("cancelled:$uid,$gpid,$gateway_module,$sms_sender,$sms_to,$sms_type,$unicode", 2, "sendsms");
		$ret['status'] = false;
		return $ret;
	}

	// fixme anton - mobile number can be anything, screened by gateway
	// $sms_sender = sendsms_getvalidnumber($sms_sender);

	// fixme anton - add a space in front of $sms_footer
	if (trim($sms_footer)) {
		$sms_footer = ' '.trim($sms_footer);
	}

	logger_print("start", 2, "sendsms");

	$ok = false;
	$p_status = 2;
	if (rate_cansend($username, $sms_to)) {
		$p_status = 0;
	}
	// we save all info first and then process with gateway module
	// the thing about this is that message saved may not be the same since gateway may not be able to process
	// message with that length or certain characters in the message are not supported by the gateway
	$db_query = "
		INSERT INTO "._DB_PREF_."_tblSMSOutgoing 
		(uid,p_gpid,p_gateway,p_src,p_dst,p_footer,p_msg,p_datetime,p_status,p_sms_type,unicode) 
		VALUES ('$uid','$gpid','$gateway_module','$sms_sender','$sms_to','$sms_footer','$sms_msg','$sms_datetime','$p_status','$sms_type','$unicode')
	";
	logger_print("saving:$uid,$gpid,$gateway_module,$sms_sender,$sms_to,$sms_type,$unicode,$p_status", 2, "sendsms");
	// continue to gateway only when save to db is true
	if ($smslog_id = @dba_insert_id($db_query)) {
		logger_print("smslog_id:".$smslog_id." saved", 2, "sendsms");
		// if pending (p_status=0) then continue, if failed (p_status=2) print log
		if ($p_status == 0) {
			logger_print("final message:".$sms_msg.$sms_footer." len:".strlen($sms_msg.$sms_footer), 3, "sendsms");
			if (x_hook($gateway_module, 'sendsms', array($sms_sender,$sms_footer,$sms_to,$sms_msg,$uid,$gpid,$smslog_id,$sms_type,$unicode))) {
				// fixme anton - deduct user's credit as soon as gateway returns true
				rate_deduct($smslog_id);
				$ok = true;
			}
		} else {
			logger_print("fail not enough credit smslog_id:".$smslog_id, 2, "sendsms");
		}
	} else {
		logger_print("fail to save in db table", 2, "sendsms");
	}

	logger_print("end", 2, "sendsms");

	$ret['status'] = $ok;
	$ret['smslog_id'] = $smslog_id;
	return $ret;
}

function sendsms_pv($username,$sms_to,$message,$sms_type='text',$unicode=0) {
	global $apps_path, $core_config, $gateway_module;

	$user = $core_config['user'];
	if ($username && ($user['username'] != $username)) {
		$user = user_getdatabyusername($username);
	}

	$uid = $user['uid'];
	$sms_sender = sendsms_get_sender($username);
	$sms_footer = $user['footer'];
	$max_length = ( $unicode ?  $user['opt']['max_sms_length_unicode'] : $user['opt']['max_sms_length'] );
	if (strlen($message)>$max_length) {
		$message = substr ($message,0,$max_length);
	}
	$sms_msg = $message;

	logger_print("start uid:".$uid." sender:".$sms_sender." footer:".$sms_footer." maxlength:".$max_length." msgcount:".strlen($sms_msg)." message:".$sms_msg, 3, "sendsms pv");

	// create a queue
	$queue_code = sendsms_queue_create($sms_sender,$sms_footer,$sms_msg,$uid,0,$sms_type,$unicode);
	if (! $queue_code) {
		// when unable to create a queue then immediately returns FALSE, no point to continue
		logger_print("fail to finalize queue creation, exit immediately", 2, "sendsms_pv");
		return FALSE;
	}

	if (is_array($sms_to)) {
		$array_sms_to = $sms_to;
	} else {
		$array_sms_to[0] = $sms_to;
	}

	for ($i=0;$i<count($array_sms_to);$i++) {
		if (substr($array_sms_to[$i], 0, 5) == 'gpid_') {
			$c_gpid = substr($array_sms_to[$i], 5);
			$rows = phonebook_getdatabyid($c_gpid);
			foreach ($rows as $key => $db_row) {
				$all_sms_to[] = $db_row['p_num'];
			}
		} else {
			$all_sms_to[] = $array_sms_to[$i];
		}
	}
	// remove double entries
	$all_sms_to = array_unique($all_sms_to);

	for ($i=0;$i<count($all_sms_to);$i++) {
		$c_sms_to = sendsms_getvalidnumber($all_sms_to[$i]);
		$ok[$i] = sendsms_queue_push($queue_code,$c_sms_to);
		$to[$i] = $c_sms_to;
		$smslog_id[$i] = 0;
		$queue[$i] = $queue_code;
	}

	if (! $core_config['issendsmsd']) {
		unset($ok);
		unset($to);
		unset($queue);
		logger_print("sendsmsd off immediately process queue_code:".$queue_code, 2, "sendsms pv");
		list($ok, $to, $smslog_id, $queue) = sendsmsd($queue_code);
	}

	logger_print("end queue_code:".$queue_code, 2, "sendsms pv");

	return array($ok, $to, $smslog_id, $queue);
}

function sendsms_bc($username,$gpid,$message,$sms_type='text',$unicode=0) {
	global $apps_path, $core_config, $gateway_module;

	$user = $core_config['user'];
	if ($username && ($user['username'] != $username)) {
		$user = user_getdatabyusername($username);
	}

	$uid = $user['uid'];
	$sms_sender = sendsms_get_sender($username);
	$sms_footer = $user['footer'];
	$max_length = ( $unicode ?  $user['opt']['max_sms_length_unicode'] : $user['opt']['max_sms_length'] );
	if (strlen($message)>$max_length) {
		$message = substr ($message,0,$max_length);
	}
	$sms_msg = $message;

	logger_print("start uid:".$uid." gpid:".$gpid." sender:".$sms_sender." footer:".$sms_footer." maxlength:".$max_length." msgcount:".strlen($sms_msg)." message:".$sms_msg, 3, "sendsms bc");

	// destination group should be an array, if single then make it array of 1 member
	if (is_array($gpid)) {
		$array_gpid = $gpid;
	} else {
		$array_gpid[0] = $gpid;
	}

	// create a queue
	$queue_code = sendsms_queue_create($sms_sender,$sms_footer,$sms_msg,$uid,$gpid,$sms_type,$unicode);
	if (! $queue_code) {
		// when unable to create a queue then immediately returns FALSE, no point to continue
		logger_print("fail to finalize queue creation, exit immediately", 2, "sendsms_bc");
		return FALSE;
	}

	$j=0;
	for ($i=0;$i<count($array_gpid);$i++) {
		$c_gpid = strtoupper($array_gpid[$i]);
		$rows = phonebook_getdatabyid($c_gpid);
		foreach ($rows as $key => $db_row) {
			$p_num = $db_row['p_num'];
			$sms_to = sendsms_getvalidnumber($p_num);
			$ok[$j] = sendsms_queue_push($queue_code,$sms_to);
			$to[$j] = $sms_to;
			$smslog_id[$i] = 0;
			$queue[$j] = $queue_code;
			$j++;
		}
	}

	if (! $core_config['issendsmsd']) {
		unset($ok);
		unset($to);
		unset($queue);
		list($ok, $to, $smslog_id, $queue) = sendsmsd($queue_code);
	}

	logger_print("end queue_code:".$queue_code, 2, "sendsms bc");

	return array($ok, $to, $smslog_id, $queue);
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
			$sms_sender = $core_config['user']['sender'];
			if ($core_config['user']['username'] != $username) {
				$sms_sender = user_getfieldbyusername($username, 'sender');
			}
		}
	}
	$sms_sender = str_replace("\'","",$sms_sender);
	$sms_sender = str_replace("\"","",$sms_sender);
	return $sms_sender;
}

function sendsms_get_template() {
	global $core_config;
	$templates = array();
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($templates = x_hook($core_config['toolslist'][$c],'sendsms_get_template')) {
			break;
		}
	}
	return $templates;
}

?>