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

function sendsms_getvalidnumber($number) {
	$number = preg_replace("/[^0-9\+]/", "", $number);
	if (strlen($number) > 20) {
		$number = substr($number, 0, 20);
	}
	return $number;
}

function sendsms_manipulate_prefix($number, $user) {
	global $core_config;
	
	_log('before prefix manipulation:[' . $number . ']', 3, 'sendsms_manipulate_prefix');
	if (is_array($user)) {
		
		$prefix = ($user['replace_zero'] ? $user['replace_zero'] : $core_config['main']['default_replace_zero']);
		$local_length = (int) $user['local_length'];
		
		// if prefix exists then replace prefix 0 with supplied prefix
		if ($prefix) {
			$number = preg_replace('/^0/', $prefix, $number);
		}
		
		// if length of number is equal to $local_length then add supplied prefix
		if (($local_length > 0) && (strlen(core_sanitize_numeric($number)) == $local_length)) {
			$number = $prefix . $number;
		}
		
		// remove plus sign
		if ($core_config['main']['plus_sign_remove']) {
			$number = str_replace('+', '', $number);
		}
		
		// remove plus sign then add single plus sign
		if ($core_config['main']['plus_sign_add']) {
			$number = str_replace('+', '', $number);
			$number = '+' . $number;
		}
	}
	_log('after prefix manipulation:[' . $number . ']', 3, 'sendsms_manipulate_prefix');
	
	return $number;
}

function sendsms_intercept($sms_sender, $sms_footer, $sms_to, $sms_msg, $uid, $gpid = 0, $sms_type = 'text', $unicode = 0, $queue_code = '', $smsc = '') {
	global $core_config;
	$ret = array();
	$ret_final = array();
	
	// feature list
	for ($c = 0; $c < count($core_config['featurelist']); $c++) {
		$c_feature = $core_config['featurelist'][$c];
		$ret = core_hook($c_feature, 'sendsms_intercept', array(
			$sms_sender,
			$sms_footer,
			$sms_to,
			$sms_msg,
			$uid,
			$gpid,
			$sms_type,
			$unicode,
			$queue_code,
			$smsc 
		));
		if ($ret['modified']) {
			$sms_sender = ($ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender);
			$sms_footer = ($ret['param']['sms_footer'] ? $ret['param']['sms_footer'] : $sms_footer);
			$sms_to = ($ret['param']['sms_to'] ? $ret['param']['sms_to'] : $sms_to);
			$sms_msg = ($ret['param']['sms_msg'] ? $ret['param']['sms_msg'] : $sms_msg);
			$uid = ($ret['param']['uid'] ? $ret['param']['uid'] : $uid);
			$gpid = ($ret['param']['gpid'] ? $ret['param']['gpid'] : $gpid);
			$sms_type = ($ret['param']['sms_type'] ? $ret['param']['sms_type'] : $sms_type);
			$unicode = ($ret['param']['unicode'] ? $ret['param']['unicode'] : $unicode);
			$queue_code = ($ret['param']['queue_code'] ? $ret['param']['queue_code'] : $queue_code);
			$smsc = ($ret['param']['smsc'] ? $ret['param']['smsc'] : $smsc);
			$ret_final['modified'] = $ret['modified'];
			$ret_final['cancel'] = $ret['cancel'];
			$ret_final['param']['sms_sender'] = $sms_sender;
			$ret_final['param']['sms_footer'] = $sms_footer;
			$ret_final['param']['sms_to'] = $sms_to;
			$ret_final['param']['sms_msg'] = $sms_msg;
			$ret_final['param']['uid'] = $uid;
			$ret_final['param']['gpid'] = $gpid;
			$ret_final['param']['sms_type'] = $sms_type;
			$ret_final['param']['unicode'] = $unicode;
			$ret_final['param']['queue_code'] = $queue_code;
			$ret_final['param']['smsc'] = $smsc;
			_log($c_feature . ' modified sms_sender:[' . $sms_sender . '] sms_footer:[' . $sms_footer . '] sms_to:[' . $sms_to . '] sms_msg:[' . $sms_msg . '] uid:[' . $uid . '] gpid:[' . $gpid . '] sms_type:[' . $sms_type . '] unicode:[' . $unicode . '] queue_code:[' . $queue_code . '] smsc:[' . $smsc . ']', 3, 'sendsms_intercept');
		}
	}
	return $ret_final;
}

function sendsms_intercept_after($status, $smslog_id, $p_status, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid, $gpid = 0, $sms_type = 'text', $unicode = 0, $queue_code = '', $smsc = '') {
	global $core_config;
	$ret = array();
	$ret_final = array();
	
	// feature list
	for ($c = 0; $c < count($core_config['featurelist']); $c++) {
		$c_feature = $core_config['featurelist'][$c];
		$ret = core_hook($c_feature, 'sendsms_intercept_after', array(
			$status,
			$smslog_id,
			$p_status,
			$sms_sender,
			$sms_footer,
			$sms_to,
			$sms_msg,
			$uid,
			$gpid,
			$sms_type,
			$unicode,
			$queue_code,
			$smsc 
		));
		if ($ret['modified']) {
			$status = ($ret['param']['status'] ? $ret['param']['status'] : $status);
			$smslog_id = ($ret['param']['smslog_id'] ? $ret['param']['smslog_id'] : $smslog_id);
			$p_status = ($ret['param']['p_status'] ? $ret['param']['p_status'] : $p_status);
			$sms_sender = ($ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender);
			$sms_footer = ($ret['param']['sms_footer'] ? $ret['param']['sms_footer'] : $sms_footer);
			$sms_to = ($ret['param']['sms_to'] ? $ret['param']['sms_to'] : $sms_to);
			$sms_msg = ($ret['param']['sms_msg'] ? $ret['param']['sms_msg'] : $sms_msg);
			$uid = ($ret['param']['uid'] ? $ret['param']['uid'] : $uid);
			$gpid = ($ret['param']['gpid'] ? $ret['param']['gpid'] : $gpid);
			$sms_type = ($ret['param']['sms_type'] ? $ret['param']['sms_type'] : $sms_type);
			$unicode = ($ret['param']['unicode'] ? $ret['param']['unicode'] : $unicode);
			$queue_code = ($ret['param']['queue_code'] ? $ret['param']['queue_code'] : $queue_code);
			$smsc = ($ret['param']['smsc'] ? $ret['param']['smsc'] : $smsc);
			$ret_final['modified'] = $ret['modified'];
			$ret_final['cancel'] = $ret['cancel'];
			$ret_final['param']['status'] = $status;
			$ret_final['param']['smslog_id'] = $smslog_id;
			$ret_final['param']['p_status'] = $p_status;
			$ret_final['param']['sms_sender'] = $sms_sender;
			$ret_final['param']['sms_footer'] = $sms_footer;
			$ret_final['param']['sms_to'] = $sms_to;
			$ret_final['param']['sms_msg'] = $sms_msg;
			$ret_final['param']['uid'] = $uid;
			$ret_final['param']['gpid'] = $gpid;
			$ret_final['param']['sms_type'] = $sms_type;
			$ret_final['param']['unicode'] = $unicode;
			$ret_final['param']['queue_code'] = $queue_code;
			$ret_final['param']['smsc'] = $smsc;
			_log($c_feature . ' modified status:[' . (int) $status . ']smslog_id:[' . $smslog_id . '] p_status:[' . (int) $p_status . '] sms_sender:[' . $sms_sender . '] sms_footer:[' . $sms_footer . '] sms_to:[' . $sms_to . '] sms_msg:[' . $sms_msg . '] uid:[' . $uid . '] gpid:[' . $gpid . '] sms_type:[' . $sms_type . '] unicode:[' . $unicode . '] queue_code:[' . $queue_code . '] smsc:[' . $smsc . ']', 3, 'sendsms_intercept_after');
		}
	}
	return $ret_final;
}

/**
 * Create SMS queue
 *
 * @global array $core_config
 * @param string $sms_sender        
 * @param string $sms_footer        
 * @param string $sms_msg        
 * @param integer $uid        
 * @param integer $gpid        
 * @param string $sms_type        
 * @param integer $unicode        
 * @param string $sms_schedule        
 * @param string $smsc        
 * @return string Queue code
 */
function sendsms_queue_create($sms_sender, $sms_footer, $sms_msg, $uid, $gpid = 0, $sms_type = 'text', $unicode = 0, $sms_schedule = '', $smsc = '') {
	global $core_config;
	
	$ret = FALSE;
	$dt = core_get_datetime();
	$sms_schedule = (trim($sms_schedule) ? core_adjust_datetime($sms_schedule) : $dt);
	$queue_code = md5(uniqid($uid . $gpid, true));
	_log("saving queue_code:" . $queue_code . " src:" . $sms_sender . " scheduled:" . core_display_datetime($sms_schedule), 2, "sendsms_queue_create");
	
	// message entering this proc already stripslashed, we need to addslashes it before saving to db
	$sms_sender = addslashes($sms_sender);
	$sms_msg = addslashes($sms_msg);
	$sms_footer = addslashes($sms_footer);
	
	$db_query = "INSERT INTO " . _DB_PREF_ . "_tblSMSOutgoing_queue ";
	$db_query .= "(queue_code,datetime_entry,datetime_scheduled,uid,gpid,sender_id,footer,message,sms_type,unicode,smsc,flag) ";
	$db_query .= "VALUES ('$queue_code','" . $dt . "','" . $sms_schedule . "','$uid','$gpid','$sms_sender','$sms_footer','$sms_msg','$sms_type','$unicode','$smsc','2')";
	if ($id = dba_insert_id($db_query)) {
		_log("saved queue_code:" . $queue_code . " id:" . $id, 2, "sendsms_queue_create");
		$ret = $queue_code;
	}
	
	return $ret;
}

function sendsms_queue_push($queue_code, $sms_to) {
	$ret = false;
	$db_query = "SELECT id FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE queue_code='$queue_code' AND flag='2'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$queue_id = $db_row['id'];
	if ($queue_id) {
		$db_query = "INSERT INTO " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst (queue_id,dst) VALUES ('$queue_id','$sms_to')";
		_log("saving queue_code:" . $queue_code . " dst:" . $sms_to, 2, "sendsms_queue_push");
		if ($smslog_id = @dba_insert_id($db_query)) {
			_log("saved queue_code:" . $queue_code . " smslog_id:" . $smslog_id, 2, "sendsms_queue_push");
			$ret = $smslog_id;
		}
	}
	return $ret;
}

function sendsms_queue_update($queue_code, $updates) {
	$ret = false;
	if (is_array($updates)) {
		$ret = dba_update(_DB_PREF_ . '_tblSMSOutgoing_queue', $updates, array(
			'queue_code' => $queue_code 
		));
	}
	return $ret;
}

function sendsmsd($single_queue = '', $sendsmsd_limit = 0, $sendsmsd_offset = 0) {
	global $core_config;
	
	if ($single_queue) {
		$queue_sql = "AND queue_code='" . $single_queue . "'";
		
		// _log("single queue queue_code:".$single_queue, 2, "sendsmsd");
	}
	$sendsmsd_limit = (int) $sendsmsd_limit;
	if ($sendsmsd_limit > 0) {
		$sql_limit = "LIMIT " . $sendsmsd_limit;
	}
	$sendsmsd_offset = (int) $sendsmsd_offset;
	if ($sendsmsd_offset > 0) {
		$sql_offset = "OFFSET " . $sendsmsd_offset;
	}
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue WHERE flag='0' " . $queue_sql . " " . $sql_limit . " " . $sql_offset;
	
	// _log("q: ".$db_query, 3, "sendsmsd");
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$c_queue_id = $db_row['id'];
		$c_queue_code = $db_row['queue_code'];
		$c_sender_id = addslashes(trim($db_row['sender_id']));
		$c_footer = addslashes(trim(htmlspecialchars_decode($db_row['footer'])));
		$c_message = addslashes(trim(htmlspecialchars_decode($db_row['message'])));
		$c_uid = $db_row['uid'];
		$c_gpid = $db_row['gpid'];
		$c_sms_type = $db_row['sms_type'];
		$c_unicode = $db_row['unicode'];
		$c_sms_count = $db_row['sms_count'];
		$c_schedule = $db_row['datetime_scheduled'];
		$c_smsc = $db_row['smsc'];
		$c_current = core_get_datetime();
		
		$continue = FALSE;
		
		// check delivery datetime
		// _log("delivery datetime qeueue:" . $c_queue_code . " scheduled:" . core_display_datetime($c_schedule) . " current:" . core_display_datetime($c_current), 3, "sendsmsd");
		if (strtotime($c_current) >= strtotime($c_schedule)) {
			$continue = TRUE;
			
			// next, check throttle limit (number of sent SMS per hour)
			if (sendsms_throttle_isoverlimit(0)) {
				$continue = FALSE;
			}
		}
		
		// process queue
		if ($continue) {
			_log("start processing queue_code:" . $c_queue_code . " sms_count:" . $c_sms_count . " scheduled:" . core_display_datetime($c_schedule) . " uid:" . $c_uid . " gpid:" . $c_gpid . " sender_id:" . $c_sender_id, 2, "sendsmsd");
			$counter = 0;
			$db_query2 = "SELECT * FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst WHERE queue_id='$c_queue_id' AND flag='0'";
			$db_result2 = dba_query($db_query2);
			while ($db_row2 = dba_fetch_array($db_result2)) {
				
				// make sure the queue is still there
				// if the queue_code with flag=0 is not exists then break, stop sendqueue
				if (!dba_isexists(_DB_PREF_ . "_tblSMSOutgoing_queue", array(
					'flag' => 0,
					'queue_code' => $c_queue_code 
				), 'AND')) {
					break;
				}
				
				$counter++;
				
				// queue_dst ID is SMS Log ID
				$c_smslog_id = $db_row2['id'];
				
				$c_dst = $db_row2['dst'];
				$c_flag = 2;
				$c_ok = false;
				_log("sending queue_code:" . $c_queue_code . " smslog_id:" . $c_smslog_id . " to:" . $c_dst . " sms_count:" . $c_sms_count . " counter:" . $counter, 2, "sendsmsd");
				$ret = sendsms_process($c_smslog_id, $c_sender_id, $c_footer, $c_dst, $c_message, $c_uid, $c_gpid, $c_sms_type, $c_unicode, $c_queue_code, $c_smsc);
				$c_dst = $ret['to'];
				if ($ret['status']) {
					$c_ok = true;
					$c_flag = 1;
					
					// add to throttle counter
					sendsms_throttle_count(0, $c_sms_count);
				}
				_log("result queue_code:" . $c_queue_code . " to:" . $c_dst . " flag:" . $c_flag . " smslog_id:" . $c_smslog_id, 2, "sendsmsd");
				$db_query3 = "UPDATE " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst SET flag='$c_flag' WHERE id='$c_smslog_id'";
				$db_result3 = dba_query($db_query3);
				$ok[] = $c_ok;
				$to[] = $c_dst;
				$smslog_id[] = $c_smslog_id;
				$queue[] = $c_queue_code;
				$counts[] = $c_sms_count;
				
				// check throttle limit (number of sent SMS per hour)
				if (sendsms_throttle_isoverlimit(0)) {
					break;
				}
			}
			
			$db_query = "SELECT count(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst WHERE queue_id='$c_queue_id' AND NOT flag ='0'";
			$db_result = dba_query($db_query);
			$db_row = dba_fetch_array($db_result);
			
			// destinations processed
			$dst_processed = (int) ($db_row['count'] ? $db_row['count'] : 0);
			
			// number of SMS processed
			$sms_processed = $dst_processed * $c_sms_count;
			
			// check whether SMS processed is >= stated SMS count in queue
			// if YES then processing queue is finished
			if ($sms_processed >= $c_sms_count) {
				$dt = core_get_datetime();
				$db_query5 = "UPDATE " . _DB_PREF_ . "_tblSMSOutgoing_queue SET flag='1', datetime_update='" . $dt . "' WHERE id='$c_queue_id'";
				if ($db_result5 = dba_affected_rows($db_query5)) {
					_log("finish processing queue_code:" . $c_queue_code . " uid:" . $c_uid . " sender_id:" . $c_sender_id . " sms_count:" . $c_sms_count, 2, "sendsmsd");
				} else {
					_log("fail to finalize process queue_code:" . $c_queue_code . " uid:" . $c_uid . " sender_id:" . $c_sender_id . " sms_processed:" . $sms_processed, 2, "sendsmsd");
				}
			} else {
				_log("partially processing queue_code:" . $c_queue_code . " uid:" . $c_uid . " sender_id:" . $c_sender_id . " sms_count:" . $c_sms_count . " sms_processed:" . $sms_processed . " counter:" . $counter, 2, "sendsmsd");
			}
		}
	}
	
	return array(
		$ok,
		$to,
		$smslog_id,
		$queue,
		$counts 
	);
}

function sendsms_process($smslog_id, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid, $gpid = 0, $sms_type = 'text', $unicode = 0, $queue_code = '', $smsc = '') {
	global $user_config;
	$ok = false;
	
	$user = $user_config;
	if ($uid && ($user['uid'] != $uid)) {
		$user = user_getdatabyuid($uid);
	}
	
	$username = $user['username'];
	
	$sms_to = sendsms_getvalidnumber($sms_to);
	
	// now on sendsms()
	// $sms_to = sendsms_manipulate_prefix($sms_to, $user);
	

	$sms_datetime = core_get_datetime();
	
	// htmlspecialchars_decode to message and footer
	$sms_msg = htmlspecialchars_decode($sms_msg);
	$sms_footer = htmlspecialchars_decode($sms_footer);
	
	// sent sms will be handled by plugins first
	$ret_intercept = sendsms_intercept($sms_sender, $sms_footer, $sms_to, $sms_msg, $uid, $gpid, $sms_type, $unicode, $queue_code, $smsc);
	if ($ret_intercept['modified']) {
		$sms_sender = ($ret_intercept['param']['sms_sender'] ? $ret_intercept['param']['sms_sender'] : $sms_sender);
		$sms_footer = ($ret_intercept['param']['sms_footer'] ? $ret_intercept['param']['sms_footer'] : $sms_footer);
		$sms_to = ($ret_intercept['param']['sms_to'] ? $ret_intercept['param']['sms_to'] : $sms_to);
		$sms_msg = ($ret_intercept['param']['sms_msg'] ? $ret_intercept['param']['sms_msg'] : $sms_msg);
		$uid = ($ret_intercept['param']['uid'] ? $ret_intercept['param']['uid'] : $uid);
		$gpid = ($ret_intercept['param']['gpid'] ? $ret_intercept['param']['gpid'] : $gpid);
		$sms_type = ($ret_intercept['param']['sms_type'] ? $ret_intercept['param']['sms_type'] : $sms_type);
		$unicode = ($ret_intercept['param']['unicode'] ? $ret_intercept['param']['unicode'] : $unicode);
		$queue_code = ($ret_intercept['param']['queue_code'] ? $ret_intercept['param']['queue_code'] : $queue_code);
		$smsc = ($ret_intercept['param']['smsc'] ? $ret_intercept['param']['smsc'] : $smsc);
	}
	
	// if hooked function returns cancel=true then stop the sending, return false
	if ($ret_intercept['cancel']) {
		_log("end with cancelled smslog_id:" . $smslog_id . " uid:" . $uid . " gpid:" . $gpid . " smsc:" . $smsc . " s:" . $sms_sender . " to:" . $sms_to . " type:" . $sms_type . " unicode:" . $unicode, 2, "sendsms_process");
		$ret['status'] = false;
		return $ret;
	}
	
	// get active gateway module as default gateway
	if (!$smsc) {
		$smsc = core_smsc_get();
	}
	
	// set no gateway if no default gateway selected
	if (!$smsc) {
		$smsc = 'blocked';
	}
	
	// get gateway
	$smsc_data = gateway_get_smscbyname($smsc);
	$gateway = $smsc_data['gateway'];
	
	// a hack to remove \r from \r\n
	// the issue begins with ENTER being \r\n and detected as 2 chars
	// and since the javascript message counter can't detect it as 2 chars
	// thus the message length counts is inaccurate
	$sms_msg = str_replace("\r\n", "\n", $sms_msg);
	
	// just to make sure its length, we need to stripslashes message before enter other procedures
	$sms_sender = stripslashes($sms_sender);
	$sms_msg = stripslashes($sms_msg);
	$sms_footer = stripslashes($sms_footer);
	
	// fixme anton - mobile number can be anything, screened by gateway
	// $sms_sender = sendsms_getvalidnumber($sms_sender);
	

	// fixme anton - add a space in front of $sms_footer
	if (trim($sms_footer)) {
		$sms_footer = ' ' . trim($sms_footer);
	}
	
	_log("start", 2, "sendsms_process");
	
	if (blacklist_mobile_isexists(0, $sms_to)) {
		_log("fail to send. mobile is in the blacklist mobile:" . $sms_to . " smslog_id:" . $smslog_id, 2, "sendsms_process");
		
		$ret['status'] = FALSE;
		$ret['to'] = $sms_to;
		$ret['smslog_id'] = $smslog_id;
		$ret['p_status'] = 2;
		
		return $ret;
	}
	
	if (rate_cansend($username, strlen($sms_msg . $sms_footer), $unicode, $sms_to)) {
		$p_status = 0;
	} else {
		_log("end with fail not enough credit smslog_id:" . $smslog_id, 2, "sendsms_process");
		$ret['status'] = true;
		
		// set TRUE to stop queue
		$ret['to'] = $sms_to;
		$ret['smslog_id'] = $smslog_id;
		$ret['p_status'] = 2;
		
		// set failed
		return $ret;
	}
	
	// message entering this proc already stripslashed, we need to addslashes it before saving to db
	$sms_sender = addslashes($sms_sender);
	$sms_msg = addslashes($sms_msg);
	$sms_footer = addslashes($sms_footer);
	
	// we save all info first and then process with gateway module
	// the thing about this is that message saved may not be the same since gateway may not be able to process
	// message with that length or certain characters in the message are not supported by the gateway
	$db_query = "
		INSERT INTO " . _DB_PREF_ . "_tblSMSOutgoing
		(smslog_id,uid,p_gpid,p_gateway,p_smsc,p_src,p_dst,p_footer,p_msg,p_datetime,p_status,p_sms_type,unicode,queue_code)
		VALUES ('$smslog_id','$uid','$gpid','$gateway','$smsc','$sms_sender','$sms_to','$sms_footer','$sms_msg','$sms_datetime','$p_status','$sms_type','$unicode','$queue_code')";
	_log("saving smslog_id:" . $smslog_id . " u:" . $uid . " g:" . $gpid . " gw:" . $gateway . " smsc:" . $smsc . " s:" . $sms_sender . " d:" . $sms_to . " type:" . $sms_type . " unicode:" . $unicode . " status:" . $p_status, 2, "sendsms");
	
	// continue to gateway only when save to db is true
	if ($id = @dba_insert_id($db_query)) {
		_log("saved smslog_id:" . $smslog_id . " id:" . $id, 2, "sendsms_process");
		if ($p_status == 0) {
			_log("final smslog_id:" . $smslog_id . " gw:" . $gateway . " smsc:" . $smsc . " message:" . $sms_msg . $sms_footer . " len:" . strlen($sms_msg . $sms_footer), 3, "sendsms");
			if (core_hook($gateway, 'sendsms', array(
				$smsc,
				$sms_sender,
				$sms_footer,
				$sms_to,
				$sms_msg,
				$uid,
				$gpid,
				$smslog_id,
				$sms_type,
				$unicode 
			))) {
				
				// fixme anton - deduct user's credit as soon as gateway returns true
				rate_deduct($smslog_id);
				$ok = true;
			} else {
				_log("fail no hook for sendsms", 2, "sendsms_process");
			}
		}
	} else {
		_log("fail to save in db table smslog_id:" . $smslog_id, 2, "sendsms_process");
	}
	
	// sent sms will be handled by plugins first
	$ret_intercept = sendsms_intercept_after($ok, $smslog_id, $p_status, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid, $gpid, $sms_type, $unicode, $queue_code, $smsc);
	if ($ret_intercept['modified']) {
		$ok = ($ret_intercept['param']['status'] ? $ret_intercept['param']['status'] : $ok);
		$smslog_id = ($ret_intercept['param']['smslog_id'] ? $ret_intercept['param']['smslog_id'] : $smslog_id);
		$p_status = ($ret_intercept['param']['p_status'] ? $ret_intercept['param']['p_status'] : $p_status);
		$sms_sender = ($ret_intercept['param']['sms_sender'] ? $ret_intercept['param']['sms_sender'] : $sms_sender);
		$sms_footer = ($ret_intercept['param']['sms_footer'] ? $ret_intercept['param']['sms_footer'] : $sms_footer);
		$sms_to = ($ret_intercept['param']['sms_to'] ? $ret_intercept['param']['sms_to'] : $sms_to);
		$sms_msg = ($ret_intercept['param']['sms_msg'] ? $ret_intercept['param']['sms_msg'] : $sms_msg);
		$uid = ($ret_intercept['param']['uid'] ? $ret_intercept['param']['uid'] : $uid);
		$gpid = ($ret_intercept['param']['gpid'] ? $ret_intercept['param']['gpid'] : $gpid);
		$sms_type = ($ret_intercept['param']['sms_type'] ? $ret_intercept['param']['sms_type'] : $sms_type);
		$unicode = ($ret_intercept['param']['unicode'] ? $ret_intercept['param']['unicode'] : $unicode);
		$queue_code = ($ret_intercept['param']['queue_code'] ? $ret_intercept['param']['queue_code'] : $queue_code);
		$smsc = ($ret_intercept['param']['smsc'] ? $ret_intercept['param']['smsc'] : $smsc);
	}
	
	_log("end", 2, "sendsms_process");
	
	$ret['status'] = $ok;
	$ret['to'] = $sms_to;
	$ret['smslog_id'] = $smslog_id;
	$ret['p_status'] = $p_status;
	
	return $ret;
}

/**
 * Send SMS helper
 *
 * @global array $core_config, $user_config
 * @param string $username        
 * @param mixed $sms_to        
 * @param string $message        
 * @param string $sms_type        
 * @param integer $unicode        
 * @param string $smsc        
 * @param boolean $nofooter        
 * @param string $sms_footer        
 * @param string $sms_sender        
 * @param string $sms_schedule        
 * @param string $reference_id        
 * @return array array($status, $sms_to, $smslog_id, $queue, $counts, $sms_count, $sms_failed, $error_strings)
 */
function sendsms_helper($username, $sms_to, $message, $sms_type = 'text', $unicode = 0, $smsc = '', $nofooter = false, $sms_footer = '', $sms_sender = '', $sms_schedule = '', $reference_id = '') {
	global $core_config, $user_config;
	
	// htmlspecialchars_decode to message and footer
	$message = htmlspecialchars_decode($message);
	$sms_footer = htmlspecialchars_decode($sms_footer);
	
	// get user data
	if ($username && ($user_config['username'] != $username)) {
		$user_config = user_getdatabyusername($username);
	}
	
	if (!is_array($sms_to)) {
		$sms_to = explode(',', $sms_to);
	}
	
	// get destinations
	for ($i = 0; $i < count($sms_to); $i++) {
		if (substr(trim($sms_to[$i]), 0, 1) == '#') {
			if ($c_group_code = substr(trim($sms_to[$i]), 1)) {
				
				$list = phonebook_search_group($user_config['uid'], $c_group_code, '', TRUE);
				$c_gpid = $list[0]['gpid'];
				$members = phonebook_getdatabyid($c_gpid);
				foreach ($members as $member) {
					if ($c_sms_to = trim($member['p_num'])) {
						$array_sms_to[] = $c_sms_to;
					}
				}
			}
		} else if (substr(trim($sms_to[$i]), 0, 1) == '@') {
			if ($c_username = substr(trim($sms_to[$i]), 1)) {
				
				// reference self will be ignored
				if ($c_username != $user_config['username']) {
					$array_username[] = $c_username;
				}
			}
		} else {
			$array_sms_to[] = trim($sms_to[$i]);
		}
	}
	
	// remove duplicates destinations
	array_unique($array_sms_to);
	
	$sms_queued = 0;
	$sms_failed = 0;
	
	// sendsms
	if (is_array($array_sms_to) && $array_sms_to[0]) {
		list($ok, $to, $smslog_id, $queue, $counts, $error_strings) = sendsms($user_config['username'], $array_sms_to, $message, $sms_type, $unicode, $smsc, $nofooter, $sms_footer, $sms_sender, $sms_schedule);
	}
	
	// fixme anton - IMs doesn't count
	// count SMSes only
	for ($i = 0; $i < count($ok); $i++) {
		if ($ok[$i]) {
			$sms_count += $counts[$i];
		} else {
			$sms_failed += $counts[$i];
		}
	}
	
	// sendsms_im
	if (is_array($array_username) && $array_username[0]) {
		$im_sender = '@' . $user_config['username'];
		foreach ($array_username as $target_user) {
			$im_sender = '@' . $user_config['username'];
			if (recvsms_inbox_add(core_get_datetime(), $im_sender, $target_user, $message, '', $reference_id)) {
				$ok[] = '1';
				$to[] = '@' . $target_user;
				$queue[] = md5($target_user . microtime());
				$sms_count++;
			}
		}
	}
	
	return array(
		$ok,
		$to,
		$smslog_id,
		$queue,
		$counts,
		$sms_count,
		$sms_failed,
		$error_strings 
	);
}

/**
 * Send SMS
 *
 * @global array $core_config, $user_config
 * @param string $username        
 * @param mixed $sms_to        
 * @param string $message        
 * @param string $sms_type        
 * @param integer $unicode        
 * @param string $smsc        
 * @param boolean $nofooter        
 * @param string $sms_footer        
 * @param string $sms_sender        
 * @param string $sms_schedule        
 * @return array array($status, $sms_to, $smslog_id, $queue, $counts, $error_strings)
 */
function sendsms($username, $sms_to, $message, $sms_type = 'text', $unicode = 0, $smsc = '', $nofooter = false, $sms_footer = '', $sms_sender = '', $sms_schedule = '') {
	global $core_config, $user_config;
	
	// htmlspecialchars_decode to message and footer
	$message = htmlspecialchars_decode($message);
	$sms_footer = htmlspecialchars_decode($sms_footer);
	
	// get user data
	$user = $user_config;
	if ($username && ($user['username'] != $username)) {
		$user = user_getdatabyusername($username);
	}
	
	if (!is_array($sms_to)) {
		$sms_to = explode(',', $sms_to);
	}
	
	$uid = $user['uid'];
	
	// discard if banned
	if (user_banned_get($uid)) {
		_log("user banned, exit immediately uid:" . $uid . ' username:' . $user['username'], 2, "sendsms");
		return array(
			FALSE,
			'',
			'',
			'',
			'',
			sprintf(_('Account %s is currently banned to use services'), $username) 
		);
	}
	
	// SMS sender ID
	$sms_sender = core_sanitize_sender($sms_sender);
	$sms_sender = (($sms_sender && sender_id_isvalid($username, $sms_sender)) ? $sms_sender : sendsms_get_sender($username));
	
	// SMS footer
	$sms_footer = core_sanitize_footer($sms_footer);
	$sms_footer = ($sms_footer ? $sms_footer : $user['footer']);
	if ($nofooter) {
		$sms_footer = '';
	}
	
	// a hack to remove \r from \r\n
	// the issue begins with ENTER being \r\n and detected as 2 chars
	// and since the javascript message counter can't detect it as 2 chars
	// thus the message length counts is inaccurate
	$message = str_replace("\r\n", "\n", $message);
	
	// just to make sure its length, we need to stripslashes message before enter other procedures
	$sms_sender = stripslashes($sms_sender);
	$message = stripslashes($message);
	$sms_footer = stripslashes($sms_footer);
	
	// fixme anton - fix #71 but not sure whats the correct solution for this
	// $max_length = ( $unicode ? $user['opt']['max_sms_length_unicode'] : $user['opt']['max_sms_length'] );
	$max_length = $user['opt']['max_sms_length'];
	
	if (strlen($message) > $max_length) {
		$message = substr($message, 0, $max_length);
	}
	$sms_msg = $message;
	
	_log("start uid:" . $uid . " sender_id:[" . $sms_sender . "] smsc:[" . $smsc . "]", 2, "sendsms");
	
	// add a space infront of footer if exists
	$c_sms_footer = (trim($sms_footer) ? ' ' . trim($sms_footer) : '');
	_log("maxlen:" . $max_length . " footerlen:" . strlen($c_sms_footer) . " footer:[" . $c_sms_footer . "] msglen:" . strlen($sms_msg) . " message:[" . $sms_msg . "]", 3, "sendsms");
	
	// create a queue
	$queue_code = sendsms_queue_create($sms_sender, $sms_footer, $sms_msg, $uid, 0, $sms_type, $unicode, $sms_schedule, $smsc);
	if (!$queue_code) {
		
		// when unable to create a queue then immediately returns FALSE, no point to continue
		_log("fail to finalize queue creation, exit immediately", 2, "sendsms");
		return array(
			FALSE,
			'',
			'',
			'',
			'',
			_('Send message failed due to unable to create queue') 
		);
	}
	
	if (is_array($sms_to)) {
		$array_sms_to = $sms_to;
	} else {
		$array_sms_to = explode(',', $sms_to);
	}
	
	// get manipulated and valid destination numbers
	$all_sms_to = array();
	for ($i = 0; $i < count($array_sms_to); $i++) {
		if ($c_sms_to = sendsms_getvalidnumber(trim($array_sms_to[$i]))) {
			$c_sms_to = sendsms_manipulate_prefix(trim($c_sms_to), $user);
			$all_sms_to[] = $c_sms_to;
		}
	}
	
	// remove double entries
	$all_sms_to = array_unique($all_sms_to);
	
	// calculate total sms and charges
	$total_count = 0;
	$total_charges = 0;
	foreach ($all_sms_to as $c_sms_to) {
		list($count, $rate, $charge) = rate_getcharges($uid, strlen($message . $c_sms_footer), $unicode, $c_sms_to);
		$total_count += $count;
		$total_charges += $charge;
	}
	_log('dst:' . count($all_sms_to) . ' sms_count:' . $total_count . ' total_charges:' . $total_charges, 2, 'sendsms');
	
	// sender's
	$credit = rate_getusercredit($user['username']);
	$balance = $credit - $total_charges;
	
	// parent's when sender is a subuser
	$parent_uid = user_getparentbyuid($user['uid']);
	if ($parent_uid) {
		$username_parent = user_uid2username($parent_uid);
		$credit_parent = rate_getusercredit($username_parent);
		$balance_parent = $credit_parent - $total_charges;
	}
	
	if ($parent_uid) {
		if (!($balance_parent >= 0)) {
			_log('failed parent do not have enough credit. credit:' . $credit_parent . ' dst:' . count($all_sms_to) . ' sms_count:' . $total_count . ' total_charges:' . $total_charges, 2, 'sendsms');
			return array(
				FALSE,
				'',
				'',
				'',
				'',
				_('Internal error please contact service provider') 
			);
		}
	} else {
		if (!($balance >= 0)) {
			_log('failed user do not have enough credit. credit:' . $credit_parent . ' dst:' . count($all_sms_to) . ' sms_count:' . $total_count . ' total_charges:' . $total_charges, 2, 'sendsms');
			return array(
				FALSE,
				'',
				'',
				'',
				'',
				_('Send message failed due to insufficient funds') 
			);
		}
	}
	
	// default returns
	for ($i = 0; $i < count($all_sms_to); $i++) {
		$ok[$i] = FALSE;
		$to[$i] = $all_sms_to[$i];
		$smslog_id[$i] = 0;
		$queue[$i] = $queue_code;
		$counts[$i] = $count;
	}
	
	$queue_count = 0;
	$sms_count = 0;
	$failed_queue_count = 0;
	$failed_sms_count = 0;
	for ($i = 0; $i < count($all_sms_to); $i++) {
		$c_sms_to = $all_sms_to[$i];
		
		$continue = TRUE;
		if (blacklist_mobile_isexists(0, $c_sms_to)) {
			$continue = FALSE;
			
			_log("fail to send. mobile is in the blacklist mobile:" . $c_sms_to, 2, "sendsms");
		}
		
		if ($continue && ($smslog_id[$i] = sendsms_queue_push($queue_code, $c_sms_to))) {
			$ok[$i] = TRUE;
			$queue_count++;
			$sms_count = $sms_count + $count;
			$error_strings[$i] = sprintf(_('Message %s has been delivered to queue'), $smslog_id[$i]);
		} else {
			$ok[$i] = FALSE;
			$failed_queue_count++;
			$failed_sms_count++;
			$error_strings[$i] = sprintf(_('Send message to %s in queue %s has failed'), $c_sms_to, $queue_code);
		}
		$to[$i] = $c_sms_to;
		$queue[$i] = $queue_code;
		$counts[$i] = $count;
	}
	
	if (sendsms_queue_update($queue_code, array(
		'flag' => '0',
		'sms_count' => $sms_count 
	))) {
		_log("end queue_code:" . $queue_code . " queue_count:" . $queue_count . " sms_count:" . $sms_count . " failed_queue:" . $failed_queue_count . " failed_sms:" . $failed_sms_count, 2, "sendsms");
	} else {
		_log("fail to prepare queue, exit immediately queue_code:" . $queue_code, 2, "sendsms");
		return array(
			FALSE,
			'',
			'',
			$queue_code,
			'',
			sprintf(_('Send message failed due to unable to prepare queue %s'), $queue_code) 
		);
	}
	
	if (!$core_config['issendsmsd']) {
		unset($ok);
		unset($to);
		unset($queue);
		unset($counts);
		_log("sendsmsd off immediately process queue_code:" . $queue_code, 2, "sendsms");
		list($ok, $to, $smslog_id, $queue, $counts) = sendsmsd($queue_code);
	}
	
	return array(
		$ok,
		$to,
		$smslog_id,
		$queue,
		$counts,
		$error_strings 
	);
}

/**
 * Send SMS to phonebook group
 *
 * @global array $core_config
 * @param string $username        
 * @param integer $gpid        
 * @param string $message        
 * @param string $sms_type        
 * @param integer $unicode        
 * @param string $smsc        
 * @param boolean $nofooter        
 * @param string $sms_footer        
 * @param string $sms_sender        
 * @param string $sms_schedule        
 * @return array array($status, $sms_to, $smslog_id, $queue, $counts)
 */
function sendsms_bc($username, $gpid, $message, $sms_type = 'text', $unicode = 0, $smsc = '', $nofooter = false, $sms_footer = '', $sms_sender = '', $sms_schedule = '') {
	global $core_config, $user_config;
	$array_sms_to = array();
	
	// get User ID
	$uid = user_username2uid($username);
	
	_log("start uid:" . $uid . " sender_id:[" . $sms_sender . "] smsc:[" . $smsc . "]", 2, "sendsms_bc");
	
	// destination group should be an array, if single then make it array of 1 member
	if (is_array($gpid)) {
		$array_gpid = $gpid;
	} else {
		$array_gpid = explode(',', $gpid);
	}
	
	$j = 0;
	for ($i = 0; $i < count($array_gpid); $i++) {
		if ($c_gpid = trim($array_gpid[$i])) {
			$sms_count = 0;
			$rows = phonebook_getdatabyid($c_gpid);
			if (is_array($rows)) {
				foreach ($rows as $key => $db_row) {
					$p_num = trim($db_row['p_num']);
					if ($sms_to = sendsms_getvalidnumber($p_num)) {
						$array_sms_to[] = $sms_to;
						$sms_count++;
					}
				}
			}
			_log("collect gpid:" . $c_gpid . " uid:" . $uid . " sender:[" . $sms_sender . "] count:" . $sms_count, 2, "sendsms_bc");
		}
	}
	
	_log("send all uid:" . $uid . " sender:[" . $sms_sender . "] count:" . count($array_sms_to), 2, "sendsms_bc");
	
	// sendsms
	if (is_array($array_sms_to) && $array_sms_to[0]) {
		list($ok, $to, $smslog_id, $queue, $counts, $error_strings) = sendsms($username, $array_sms_to, $message, $sms_type, $unicode, $smsc, $nofooter, $sms_footer, $sms_sender, $sms_schedule);
	}
	
	return array(
		$ok,
		$to,
		$smslog_id,
		$queue,
		$counts,
		$error_strings 
	);
}

function sendsms_get_sender($username, $default_sender_id = '') {
	global $core_config, $user_config;
	
	// get configured sender ID
	if ($username) {
		if ($core_config['main']['gateway_number']) {
			
			// 1st priority is "Default sender ID" from main configuration
			$sms_sender = $core_config['main']['gateway_number'];
		} else {
			
			// 2nd priority is "SMS sender ID" from user preferences
			$sms_sender = $user_config['sender'];
			if ($user_config['username'] != $username) {
				$c_sms_sender = user_getfieldbyusername($username, 'sender');
				
				// validate if $username is supplied
				if (sender_id_isvalid($username, $c_sms_sender)) {
					$sms_sender = $c_sms_sender;
				}
			}
		}
	}
	
	// configured sender ID
	$sms_sender = core_sanitize_sender($sms_sender);
	
	// supplied sender ID as default in case configured sender ID is empty
	if (!$sms_sender && $default_sender_id) {
		$sms_sender = core_sanitize_sender($default_sender_id);
	}
	
	return $sms_sender;
}

function sendsms_get_template() {
	global $core_config;
	$templates = array();
	for ($c = 0; $c < count($core_config['featurelist']); $c++) {
		if ($templates = core_hook($core_config['featurelist'][$c], 'sendsms_get_template')) {
			break;
		}
	}
	return $templates;
}

/**
 * Get SMS data from $smslog_id
 *
 * @param integer $smslog_id        
 * @return array
 */
function sendsms_get_sms($smslog_id) {
	$data = array();
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$data = $db_row;
	}
	return $data;
}

/**
 * Check send SMS throttle limit
 *
 * @param integer $uid
 *        User ID
 * @param integer $limit
 *        Number of SMS sent
 * @param integer $period
 *        Throttle period in minute (default is 60)
 * @return boolean TRUE on overlimit
 */
function sendsms_throttle_isoverlimit($uid, $limit = 0, $period = 60) {
	global $core_config;
	
	$limit = ((int) $limit ? (int) $limit : $core_config['main']['sms_limit_per_hour']);
	$period = ((int) $period ? (int) $period * 60 : 3600);
	
	if (!$limit) {
		// no limit no over limit
		return FALSE;
	}
	
	if (!$period) {
		// no period no over limit
		return FALSE;
	}
	
	// get start time, UTC
	$reg = registry_search($uid, 'core', 'sendsms', 'throttle_start');
	$start = $reg['core']['sendsms']['throttle_start'];
	
	if ($start) {
		// get sum of sent SMS over the hour
		$reg = registry_search($uid, 'core', 'sendsms', 'throttle_sum');
		$sum = $reg['core']['sendsms']['throttle_sum'];
		
		//check bucket expired
		if (strtotime($start) + $period < strtotime(core_get_datetime())) {
			// is expired
			// _log('is expired', 3, 'sendsms_throttle_isoverlimit');
			

			return FALSE;
		} else {
			// not expired
			if ((int) $sum > $limit) {
				// is over limit
				// _log('is overlimit', 3, 'sendsms_throttle_isoverlimit');
				

				return TRUE;
			} else {
				// not over limit
				_log('under quota not overlimit sum:' . $sum, 3, 'sendsms_throttle_isoverlimit');
				
				return FALSE;
			}
		}
	} else {
		_log('just started not overlimit', 3, 'sendsms_throttle_isoverlimit');
		
		return FALSE;
	}
	
	// _log('default overlimit', 3, 'sendsms_throttle_isoverlimit');
	return TRUE;
}

/**
 * Counter for throttle limit
 *
 * @param integer $uid
 *        User ID
 * @param integer $count
 *        (default is 1)
 *        Sent SMS
 * @return boolean TRUE of successful counter
 */
function sendsms_throttle_count($uid, $count = 1, $limit = 0, $period = 60) {
	global $core_config;
	
	$limit = ((int) $limit ? (int) $limit : $core_config['main']['sms_limit_per_hour']);
	$period = ((int) $period ? (int) $period * 60 : 3600);
	
	if (!$limit) {
		// no limit no over limit
		return FALSE;
	}
	
	// get start time, UTC
	$reg = registry_search($uid, 'core', 'sendsms', 'throttle_start');
	$start = $reg['core']['sendsms']['throttle_start'];
	
	if ($start) {
		// get sum of sent SMS over the hour
		$reg = registry_search($uid, 'core', 'sendsms', 'throttle_sum');
		$sum = $reg['core']['sendsms']['throttle_sum'];
		_log('throttle bucket exists start:' . core_display_datetime($start) . ' sum:' . $sum . ' limit:' . $limit, 3, 'sendsms_throttle_count');
	} else {
		$start = core_get_datetime();
		$sum = 0;
		if (registry_update($uid, 'core', 'sendsms', array(
			'throttle_start' => $start,
			'throttle_sum' => $sum 
		))) {
			_log('throttle bucket started start:' . core_display_datetime($start) . ' limit:' . $limit, 3, 'sendsms_throttle_count');
		} else {
			_log('fail to start throttle bucket', 3, 'sendsms_throttle_count');
			
			return FALSE;
		}
	}
	
	// check bucket expired
	if (strtotime($start) + $period < strtotime(core_get_datetime())) {
		// expired, create new
		$start = core_get_datetime();
		$sum = 0;
		_log('expired start:' . core_display_datetime($start), 3, 'sendsms_throttle_count');
	} else {
		//_log('not expired', 3, 'sendsms_throttle_count');
		// not expired
		if ((int) $sum <= $limit) {
			// add to bucket
			$sum += $count;
			//_log('add to bucket sum:' . $sum, 3, 'sendsms_throttle_count');
		} else {
			_log('overlimit sum:' . $sum . ' limit:' . $limit, 3, 'sendsms_throttle_count');
			
			return FALSE;
		}
	}
	
	// save in registry
	if (registry_update($uid, 'core', 'sendsms', array(
		'throttle_start' => $start,
		'throttle_sum' => $sum 
	))) {
		return TRUE;
	}
	
	return FALSE;
}
