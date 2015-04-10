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

function recvsms($sms_datetime, $sms_sender, $message, $sms_receiver = "", $smsc = '') {
	global $core_config;
	if ($core_config['isrecvsmsd']) {
		$c_isrecvsmsd = 1;
		
		// save to db and mark as queued (flag_processed = 1)
		$ret = dba_add(_DB_PREF_ . '_tblRecvSMS', array(
			'flag_processed' => 1,
			'sms_datetime' => core_adjust_datetime($sms_datetime),
			'sms_sender' => $sms_sender,
			'message' => $message,
			'sms_receiver' => $sms_receiver,
			'smsc' => $smsc 
		));
	} else {
		$c_isrecvsmsd = 0;
		
		// save to db but mark as processed (flag_processed = 2) and then directly call setsmsincomingaction()
		$ret = dba_add(_DB_PREF_ . '_tblRecvSMS', array(
			'flag_processed' => 2,
			'sms_datetime' => core_adjust_datetime($sms_datetime),
			'sms_sender' => $sms_sender,
			'message' => $message,
			'sms_receiver' => $sms_receiver,
			'smsc' => $smsc 
		));
		setsmsincomingaction(core_display_datetime($sms_datetime), $sms_sender, $message, $sms_receiver, $smsc);
	}
	logger_print("isrecvsmsd:" . $c_isrecvsmsd . " dt:" . $sms_datetime . " sender:" . $sms_sender . " m:" . $message . " receiver:" . $sms_receiver . " smsc:" . $smsc, 3, "recvsms");
	return $ret;
}

function recvsmsd() {
	global $core_config;
	$core_config['recvsmsd_limit'] = ((int) $core_config['recvsmsd_limit'] ? (int) $core_config['recvsmsd_limit'] : 200);
	$list = dba_search(_DB_PREF_ . '_tblRecvSMS', '*', array(
		'flag_processed' => 1 
	), '', array(
		'LIMIT' => $core_config['recvsmsd_limit'] 
	));
	$j = 0;
	for ($j = 0; $j < count($list); $j++) {
		if ($id = $list[$j]['id']) {
			$sms_datetime = $list[$j]['sms_datetime'];
			$sms_sender = $list[$j]['sms_sender'];
			$message = $list[$j]['message'];
			$sms_receiver = $list[$j]['sms_receiver'];
			$smsc = $list[$j]['smsc'];
			if (dba_update(_DB_PREF_ . '_tblRecvSMS', array(
				'flag_processed' => 2 
			), array(
				'id' => $id 
			))) {
				logger_print("id:" . $id . " dt:" . core_display_datetime($sms_datetime) . " sender:" . $sms_sender . " m:" . $message . " receiver:" . $sms_receiver . " smsc:" . $smsc, 3, "recvsmsd");
				setsmsincomingaction(core_display_datetime($sms_datetime), $sms_sender, $message, $sms_receiver, $smsc);
			}
		}
	}
}

/**
 * Check available keyword or keyword that hasn't been added
 *
 * @param $keyword keyword        
 * @return TRUE if available, FALSE if already exists or not available
 */
function checkavailablekeyword($keyword) {
	global $reserved_keywords, $core_config;
	$ok = true;
	$reserved = false;
	$keyword = trim(strtoupper($keyword));
	for ($i = 0; $i < count($reserved_keywords); $i++) {
		if ($keyword == trim(strtoupper($reserved_keywords[$i]))) {
			$reserved = true;
		}
	}
	
	// if reserved returns not available, FALSE
	if ($reserved) {
		$ok = false;
	} else {
		for ($c = 0; $c < count($core_config['featurelist']); $c++) {
			
			// checkavailablekeyword() on hooks will return TRUE as well if keyword is available
			// so we're looking for FALSE value
			if (core_hook($core_config['featurelist'][$c], 'checkavailablekeyword', array(
				$keyword 
			)) === FALSE) {
				$ok = false;
				break;
			}
		}
	}
	return $ok;
}

function recvsms_intercept($sms_datetime, $sms_sender, $message, $sms_receiver = '', $smsc = '') {
	global $core_config;
	$ret = array();
	$ret_final = array();
	
	// feature list
	for ($c = 0; $c < count($core_config['featurelist']); $c++) {
		$ret = core_hook($core_config['featurelist'][$c], 'recvsms_intercept', array(
			$sms_datetime,
			$sms_sender,
			$message,
			$sms_receiver,
			$smsc 
		));
		if ($ret['modified']) {
			$sms_datetime = ($ret['param']['sms_datetime'] ? $ret['param']['sms_datetime'] : $sms_datetime);
			$sms_sender = ($ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender);
			$message = ($ret['param']['message'] ? $ret['param']['message'] : $message);
			$sms_receiver = ($ret['param']['sms_receiver'] ? $ret['param']['sms_receiver'] : $sms_receiver);
			$smsc = ($ret['param']['smsc'] ? $ret['param']['smsc'] : $smsc);
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
			$ret_final['param']['smsc'] = $ret['param']['smsc'];
			$ret_final['modified'] = TRUE;
		}
		if ($ret['uid']) {
			$ret_final['uid'] = $ret['uid'];
		}
		if ($ret['hooked']) {
			$ret_final['hooked'] = $ret['hooked'];
		}
		if ($ret['cancel']) {
			$ret_final['cancel'] = TRUE;
			return $ret_final;
		}
	}
	return $ret_final;
}

function recvsms_intercept_after($sms_datetime, $sms_sender, $message, $sms_receiver = "", $feature, $status, $uid, $smsc = '') {
	global $core_config;
	$ret = array();
	$ret_final = array();
	
	// feature list
	for ($c = 0; $c < count($core_config['featurelist']); $c++) {
		$ret = core_hook($core_config['featurelist'][$c], 'recvsms_intercept_after', array(
			$sms_datetime,
			$sms_sender,
			$message,
			$sms_receiver,
			$feature,
			$status,
			$uid,
			$smsc 
		));
		if ($ret['modified']) {
			$sms_datetime = ($ret['param']['sms_datetime'] ? $ret['param']['sms_datetime'] : $sms_datetime);
			$sms_sender = ($ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender);
			$message = ($ret['param']['message'] ? $ret['param']['message'] : $message);
			$sms_receiver = ($ret['param']['sms_receiver'] ? $ret['param']['sms_receiver'] : $sms_receiver);
			$smsc = ($ret['param']['smsc'] ? $ret['param']['smsc'] : $smsc);
			$feature = ($ret['param']['feature'] ? $ret['param']['feature'] : $feature);
			$status = ($ret['param']['status'] ? $ret['param']['status'] : $status);
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
			$ret_final['param']['feature'] = $ret['param']['feature'];
			$ret_final['param']['status'] = $ret['param']['status'];
			$ret_final['param']['smsc'] = $ret['param']['smsc'];
			$ret_final['modified'] = TRUE;
		}
		if ($ret['uid']) {
			$ret_final['uid'] = $ret['uid'];
		}
		if ($ret['hooked']) {
			$ret_final['hooked'] = $ret['hooked'];
		}
		if ($ret['cancel']) {
			$ret_final['cancel'] = TRUE;
			return $ret_final;
		}
	}
	return $ret_final;
}

function setsmsincomingaction($sms_datetime, $sms_sender, $message, $sms_receiver = '', $smsc = '') {
	global $core_config;
	
	// blacklist
	if (blacklist_mobile_isexists(0, $sms_sender)) {
		logger_print("incoming SMS discarded sender is in the blacklist datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:[" . $message . "]  smsc:" . $smsc, 3, "setsmsincomingaction");
		return false;
	}
	
	// incoming sms will be handled by plugins first
	$ret_intercept = recvsms_intercept($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
	if ($ret_intercept['modified']) {
		$sms_datetime = ($ret_intercept['param']['sms_datetime'] ? $ret_intercept['param']['sms_datetime'] : $sms_datetime);
		$sms_sender = ($ret_intercept['param']['sms_sender'] ? $ret_intercept['param']['sms_sender'] : $sms_sender);
		$message = ($ret_intercept['param']['message'] ? $ret_intercept['param']['message'] : $message);
		$sms_receiver = ($ret_intercept['param']['sms_receiver'] ? $ret_intercept['param']['sms_receiver'] : $sms_receiver);
		$smsc = ($ret_intercept['param']['smsc'] ? $ret_intercept['param']['smsc'] : $smsc);
	}
	
	// set active gateway module as default gateway
	// if (!$smsc) {
	// $smsc = core_smsc_get();
	// }
	

	// log it
	logger_print("dt:" . $sms_datetime . " sender:" . $sms_sender . " m:" . $message . " receiver:" . $sms_receiver . ' smsc:' . $smsc, 3, "setsmsincomingaction");
	
	// if hooked function returns cancel=true then stop the processing incoming sms, return false
	if ($ret_intercept['cancel']) {
		logger_print("cancelled datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:[" . $message . "]  smsc:" . $smsc, 3, "setsmsincomingaction");
		return false;
	}
	
	$c_uid = 0;
	$c_feature = "";
	$ok = false;
	$keyword_separator = ($core_config['main']['keyword_separator'] ? $core_config['main']['keyword_separator'] : ' ');
	$array_target_keyword = explode($keyword_separator, $message);
	$target_keyword = strtoupper(trim($array_target_keyword[0]));
	$raw_message = $message;
	$message = $array_target_keyword[1];
	for ($i = 2; $i < count($array_target_keyword); $i++) {
		$message .= " " . $array_target_keyword[$i];
	}
	switch ($target_keyword) {
		case "BC":
			$c_uid = user_mobile2uid($sms_sender);
			$c_username = user_uid2username($c_uid);
			$c_feature = 'core';
			$array_target_group = explode(" ", $message);
			$target_group = strtoupper(trim($array_target_group[0]));
			$list = phonebook_search_group($c_uid, $target_group, '', TRUE);
			$c_gpid = $list[0]['gpid'];
			$message = $array_target_group[1];
			for ($i = 2; $i < count($array_target_group); $i++) {
				$message .= " " . $array_target_group[$i];
			}
			logger_print("bc username:" . $c_username . " gpid:" . $c_gpid . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:" . $message . " raw:" . $raw_message, 3, "setsmsincomingaction");
			if ($c_username && $c_gpid && $message) {
				list($ok, $to, $smslog_id, $queue) = sendsms_bc($c_username, $c_gpid, $message);
				$ok = true;
			} else {
				_log('bc has failed due to missing option u:' . $c_username . ' gpid:' . $c_gpid . ' m:[' . $message . ']', 3, 'setsmsincomingaction');
			}
			break;
		
		default :
			for ($c = 0; $c < count($core_config['featurelist']); $c++) {
				$c_feature = $core_config['featurelist'][$c];
				$ret = core_hook($c_feature, 'setsmsincomingaction', array(
					$sms_datetime,
					$sms_sender,
					$target_keyword,
					$message,
					$sms_receiver,
					$smsc,
					$raw_message 
				));
				if ($ok = $ret['status']) {
					$c_uid = $ret['uid'];
					logger_print("feature:" . $c_feature . " datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " keyword:" . $target_keyword . " message:" . $message . " raw:" . $raw_message . " smsc:" . $smsc, 3, "setsmsincomingaction");
					break;
				}
			}
	}
	$c_status = ($ok ? 1 : 0);
	if ($c_status == 0) {
		$c_feature = '';
		$target_keyword = '';
		$message = $raw_message;
		
		// from recvsms_intercept(), force status as 'handled'
		if ($ret_intercept['hooked']) {
			$c_status = 1;
			if ($ret_intercept['uid']) {
				$c_uid = $ret_intercept['uid'];
			}
			logger_print("intercepted datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:" . $message, 3, "setsmsincomingaction");
		} else {
			logger_print("unhandled datetime:" . $sms_datetime . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " message:" . $message, 3, "setsmsincomingaction");
		}
	}
	
	// incoming sms intercept after
	unset($ret_intercept);
	$ret_intercept = recvsms_intercept_after($sms_datetime, $sms_sender, $message, $sms_receiver, $c_feature, $c_status, $c_uid, $smsc);
	if ($ret_intercept['modified']) {
		$sms_datetime = ($ret_intercept['param']['sms_datetime'] ? $ret_intercept['param']['sms_datetime'] : $sms_datetime);
		$sms_sender = ($ret_intercept['param']['sms_sender'] ? $ret_intercept['param']['sms_sender'] : $sms_sender);
		$message = ($ret_intercept['param']['message'] ? $ret_intercept['param']['message'] : $message);
		$sms_receiver = ($ret_intercept['param']['sms_receiver'] ? $ret_intercept['param']['sms_receiver'] : $sms_receiver);
		$c_feature = ($ret_intercept['param']['feature'] ? $ret_intercept['param']['feature'] : $c_feature);
		$c_status = ($ret_intercept['param']['status'] ? $ret_intercept['param']['status'] : $c_status);
		$c_uid = ($ret_intercept['param']['uid'] ? $ret_intercept['param']['uid'] : $c_uid);
		$smsc = ($ret_intercept['param']['smsc'] ? $ret_intercept['param']['smsc'] : $smsc);
	}
	
	// fixme anton - all incoming messages set to user with uid=1 if no one owns it
	$c_uid = ($c_uid ? $c_uid : 1);
	
	$db_query = "
		INSERT INTO " . _DB_PREF_ . "_tblSMSIncoming
		(in_uid,in_feature,in_gateway,in_sender,in_receiver,in_keyword,in_message,in_datetime,in_status)
		VALUES
		('$c_uid','$c_feature','$smsc','$sms_sender','$sms_receiver','$target_keyword','$message','" . core_adjust_datetime($sms_datetime) . "','$c_status')";
	$db_result = dba_query($db_query);
	
	return $ok;
}

function recvsms_inbox_add_intercept($sms_datetime, $sms_sender, $target_user, $message, $sms_receiver = "", $reference_id = '') {
	global $core_config;
	$ret = array();
	$ret_final = array();
	
	// feature list
	for ($c = 0; $c < count($core_config['featurelist']); $c++) {
		$ret = core_hook($core_config['featurelist'][$c], 'recvsms_inbox_add_intercept', array(
			$sms_datetime,
			$sms_sender,
			$target_user,
			$message,
			$sms_receiver,
			$reference_id 
		));
		if ($ret['modified']) {
			$ret_final['modified'] = $ret['modified'];
			$ret_final['param']['sms_datetime'] = $ret['param']['sms_datetime'];
			$ret_final['param']['sms_sender'] = $ret['param']['sms_sender'];
			$ret_final['param']['target_user'] = $ret['param']['target_user'];
			$ret_final['param']['message'] = $ret['param']['message'];
			$ret_final['param']['sms_receiver'] = $ret['param']['sms_receiver'];
			$ret_final['param']['reference_id'] = $ret['param']['reference_id'];
			$sms_datetime = ($ret['param']['sms_datetime'] ? $ret['param']['sms_datetime'] : $sms_datetime);
			$sms_sender = ($ret['param']['sms_sender'] ? $ret['param']['sms_sender'] : $sms_sender);
			$target_user = ($ret['param']['target_user'] ? $ret['param']['target_user'] : $target_user);
			$message = ($ret['param']['message'] ? $ret['param']['message'] : $message);
			$sms_receiver = ($ret['param']['sms_receiver'] ? $ret['param']['sms_receiver'] : $sms_receiver);
			$reference_id = ($ret['param']['reference_id'] ? $ret['param']['reference_id'] : $reference_id);
		}
		if ($ret['uid']) {
			$ret_final['uid'] = $ret['uid'];
		}
		if ($ret['hooked']) {
			$ret_final['hooked'] = $ret['hooked'];
		}
	}
	return $ret_final;
}

function recvsms_inbox_add($sms_datetime, $sms_sender, $target_user, $message, $sms_receiver = "", $reference_id = '') {
	global $core_config;
	
	// sms to inbox will be handled by plugins first
	$ret_intercept = recvsms_inbox_add_intercept($sms_datetime, $sms_sender, $target_user, $message, $sms_receiver, $reference_id);
	if ($ret_intercept['param_modified']) {
		$sms_datetime = ($ret_intercept['param']['sms_datetime'] ? $ret_intercept['param']['sms_datetime'] : $sms_datetime);
		$sms_sender = ($ret_intercept['param']['sms_sender'] ? $ret_intercept['param']['sms_sender'] : $sms_sender);
		$target_user = ($ret_intercept['param']['target_user'] ? $ret_intercept['param']['target_user'] : $target_user);
		$message = ($ret_intercept['param']['message'] ? $ret_intercept['param']['message'] : $message);
		$sms_receiver = ($ret_intercept['param']['sms_receiver'] ? $ret_intercept['param']['sms_receiver'] : $sms_receiver);
		$reference_id = ($ret_intercept['param']['reference_id'] ? $ret_intercept['param']['reference_id'] : $reference_id);
	}
	
	$ok = FALSE;
	if ($sms_sender && $target_user && $message) {
		$user = user_getdatabyusername($target_user);
		if ($uid = $user['uid']) {
			
			// discard if banned
			if (user_banned_get($uid)) {
				logger_print("user banned, message ignored uid:" . $uid, 2, "recvsms_inbox_add");
				return FALSE;
			}
			
			// get name from target_user's phonebook
			$c_name = '';
			if (substr($sms_sender, 0, 1) == '@') {
				$c_username = str_replace('@', '', $sms_sender);
				$c_name = user_getfieldbyusername($c_username, 'name');
			} else {
				$c_name = phonebook_number2name($uid, $sms_sender);
			}
			$sender = $c_name ? $c_name . ' (' . $sms_sender . ')' : $sms_sender;
			
			// forward to Inbox
			if ($fwd_to_inbox = $user['fwd_to_inbox']) {
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_tblSMSInbox
					(in_sender,in_receiver,in_uid,in_msg,in_datetime,reference_id)
					VALUES ('$sms_sender','$sms_receiver','$uid','$message','" . core_adjust_datetime($sms_datetime) . "','$reference_id')
				";
				logger_print("saving sender:" . $sms_sender . " receiver:" . $sms_receiver . " target:" . $target_user . " reference_id:" . $reference_id, 2, "recvsms_inbox_add");
				if ($inbox_id = @dba_insert_id($db_query)) {
					logger_print("saved id:" . $inbox_id . " sender:" . $sms_sender . " receiver:" . $sms_receiver . " target:" . $target_user, 2, "recvsms_inbox_add");
					$ok = TRUE;
				}
			}
			
			// forward to email, consider site config too
			if ($parent_uid = user_getparentbyuid($uid)) {
				$site_config = site_config_get($parent_uid);
			}
			
			$web_title = ($site_config['web_title'] ? $site_config['web_title'] : $core_config['main']['web_title']);
			$email_service = ($site_config['email_service'] ? $site_config['email_service'] : $core_config['main']['email_service']);
			$email_footer = ($site_config['email_footer'] ? $site_config['email_footer'] : $core_config['main']['email_footer']);
			
			$sms_receiver = ($sms_receiver ? $sms_receiver : '-');
			
			if ($fwd_to_email = $user['fwd_to_email']) {
				if ($email = $user['email']) {
					$subject = _('Message from') . " " . $sender;
					$body = $web_title . "\n\n";
					$body .= _('Message received at') . " " . $sms_receiver . " " . _('on') . " " . $sms_datetime . "\n\n";
					$body .= _('From') . " " . $sender . "\n\n";
					$body .= $message . "\n\n--\n";
					$body .= $email_footer . "\n\n";
					$body = stripslashes($body);
					logger_print("send email from:" . $email_service . " to:" . $email . " message:[" . $message . "]", 3, "recvsms_inbox_add");
					$data = array(
						'mail_from_name' => $web_title,
						'mail_from' => $email_service,
						'mail_to' => $email,
						'mail_subject' => $subject,
						'mail_body' => $body 
					);
					sendmail($data);
					logger_print("sent email from:" . $email_service . " to:" . $email, 3, "recvsms_inbox_add");
				}
			}
			
			// forward to mobile
			if ($fwd_to_mobile = $user['fwd_to_mobile']) {
				if ($mobile = $user['mobile']) {
					
					// fixme anton
					$c_message = $message . ' ' . $sender;
					if ($sender_uid = user_mobile2uid($sms_sender)) {
						if ($sender_username = user_uid2username($sender_uid)) {
							$c_message = $message . ' ' . '@' . $sender_username;
						}
					}
					$message = $c_message;
					$unicode = core_detect_unicode($message);
					$nofooter = TRUE;
					
					logger_print("send to mobile:" . $mobile . " from:" . $sms_sender . " user:" . $target_user . " message:" . $message, 3, "recvsms_inbox_add");
					list($ok, $to, $smslog_id, $queue) = sendsms($target_user, $mobile, $message, 'text', $unicode, '', $nofooter);
					if ($ok[0] == 1) {
						logger_print("sent to mobile:" . $mobile . " from:" . $sms_sender . " user:" . $target_user, 2, "recvsms_inbox_add");
					}
				}
			}
		}
	}
	return $ok;
}

function getsmsinbox() {
	$smscs = gateway_getall_smsc_names();
	foreach ($smscs as $smsc) {
		$smsc_data = gateway_get_smscbyname($smsc);
		$gateways[] = $smsc_data['gateway'];
	}
	if (is_array($gateways)) {
		$gateways = array_unique($gateways);
		foreach ($gateways as $gateway) {
			core_hook($gateway, 'getsmsinbox');
		}
	}
}
