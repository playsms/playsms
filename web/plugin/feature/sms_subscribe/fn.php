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

/**
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword checkavailablekeyword()
 *        will insert keyword for checking to the hook here
 * @return TRUE if keyword is available
 *
 */
function sms_subscribe_hook_checkavailablekeyword($keyword) {
	$ok = true;
	$db_query = "SELECT subscribe_id FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_keyword='$keyword'";
	if ($db_result = dba_num_rows($db_query)) {
		$ok = false;
	}
	return $ok;
}

/*
 * Implementations of hook setsmsincomingaction() @param $sms_datetime date and time when incoming sms inserted to playsms @param $sms_sender sender on incoming sms @param $subscribe_keyword check if keyword is for sms_subscribe @param $subscribe_param get parameters from incoming sms @param $sms_receiver receiver number that is receiving incoming sms @return $ret array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_subscribe_hook_setsmsincomingaction($sms_datetime, $sms_sender, $subscribe_keyword, $subscribe_param = '', $sms_receiver = '', $smsc = '', $raw_message = '') {
	$ok = false;
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_keyword='$subscribe_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		if ($db_row['uid'] && $db_row['subscribe_enable']) {
			_log('begin k:' . $subscribe_keyword . ' c:' . $subscribe_param, 2, 'sms_subscribe');
			if (sms_subscribe_handle($db_row, $sms_datetime, $sms_sender, $subscribe_keyword, $subscribe_param, $sms_receiver, $smsc, $raw_message)) {
				$ok = true;
			}
			$status = ($ok ? 'handled' : 'unhandled');
			_log('end k:' . $subscribe_keyword . ' c:' . $subscribe_param . ' s:' . $status, 2, 'sms_subscribe');
		}
	}
	$ret['uid'] = $db_row['uid'];
	$ret['status'] = $ok;
	return $ret;
}

function sms_subscribe_handle($list, $sms_datetime, $sms_sender, $subscribe_keyword, $subscribe_param = '', $sms_receiver = '', $smsc = '', $raw_message = '') {
	global $core_config;
	$c_uid = $list['uid'];
	$subscribe_keyword = strtoupper(trim($subscribe_keyword));
	$subscribe_param = trim($subscribe_param);
	$username = user_uid2username($c_uid);
	_log("username:" . $username . " sender:" . $sms_sender . " keyword:" . $subscribe_keyword . " param:" . $subscribe_param, 3, "sms_subscribe");
	$subscribe_accept_param = $list['subscribe_param'];
	$subscribe_reject_param = $list['unsubscribe_param'];
	$forward_param = $list['forward_param'];
	$smsc = gateway_decide_smsc($smsc, $list['smsc']);
	
	// for later use
	$subscribe_param_array = explode(" ", $subscribe_param);
	$forward_sms = '';
	for ($i = 1; $i < sizeof($subscribe_param_array); $i++) {
		$forward_sms .= $subscribe_param_array[$i] . ' ';
	}
	$forward_sms = substr($forward_sms, 0, -1);
	
	// check for BC sub-keyword
	$subscribe_id = $list['subscribe_id'];
	$c_arr = explode(' ', $subscribe_param);
	
	// check for BC/forward param
	$bc = trim(strtoupper($c_arr[0]));
	if ((($bc == 'BC') || ($forward_param && ($bc == $forward_param))) && ($c_uid == user_mobile2uid($sms_sender))) {
		for ($i = 1; $i < count($c_arr); $i++) {
			$msg0 .= $c_arr[$i] . ' ';
		}
		$message = trim($msg0);
		$bc_to = '';
		$db_query1 = "SELECT member_number FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '$subscribe_id'";
		$db_result1 = dba_query($db_query1);
		while ($db_row1 = dba_fetch_array($db_result1)) {
			$bc_to[] = $db_row1['member_number'];
		}
		if (is_array($bc_to) && count($bc_to) > 0) {
			$unicode = core_detect_unicode($message);
			_log('BC sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' count:' . count($bc_to) . ' m:' . $message, 3, "sms_subscribe");
			$message = addslashes($message);
			list($ok, $to, $smslog_id, $queue) = sendsms_helper($username, $bc_to, $message, 'text', $unicode, $smsc, TRUE);
			return true;
		} else {
			return false;
		}
	}
	
	// check for subscribe/unsubscribe sub-keyword
	$ok = false;
	$subscribe_param = trim(strtoupper($subscribe_param));
	if ($sms_to = $sms_sender) {
		$msg1 = addslashes($list['subscribe_msg']);
		$msg2 = addslashes($list['unsubscribe_msg']);
		$unknown_format_msg = addslashes($list['unknown_format_msg']);
		$already_member_msg = addslashes($list['already_member_msg']);
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE member_number='$sms_to' AND subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$num_rows = (dba_num_rows($db_query) ? 1 : 0);
		if ($num_rows == 0) {
			$member = false;
			switch ($subscribe_param) {
				case "ON":
				case "IN":
				case "REG":
				case $subscribe_accept_param:
					$message = $msg1;
					$db_query = "INSERT INTO " . _DB_PREF_ . "_featureSubscribe_member (subscribe_id,member_number,member_since) VALUES ('$subscribe_id','$sms_to','" . core_get_datetime() . "')";
					$logged = dba_query($db_query);
					_log('REG SUCCESS sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to . ' m:[' . $message . ']', 2, "sms_subscribe");
					$ok = true;
					break;
				
				default :
					$message = $unknown_format_msg;
					_log('Unknown format sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to, 2, "sms_subscribe");
					$ok = true;
					break;
			}
		} else {
			$member = true;
			switch ($subscribe_param) {
				case "OFF":
				case "OUT":
				case "UNREG":
				case $subscribe_reject_param:
					$message = $msg2;
					$success = 'fail to delete member';
					$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE member_number='$sms_to' AND subscribe_id='$subscribe_id'";
					$deleted = dba_query($db_query);
					$success = 'FAILED';
					if ($deleted) {
						$success = 'SUCCESS';
						$ok = true;
					}
					_log('UNREG ' . $success . ' sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to . ' m:[' . $message . ']', 2, "sms_subscribe");
					break;
				
				case "ON":
				case "IN":
				case "REG":
				case $subscribe_accept_param:
					$message = $already_member_msg;
					_log('REG fail already a member sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to . ' m:[' . $message . ']', 2, "sms_subscribe");
					$ok = true;
					break;
				
				default :
					$message = $unknown_format_msg;
					_log('Unknown format sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to, 2, "sms_subscribe");
					$ok = true;
					break;
			}
		}
		if ($message) {
			$message = addslashes($message);
			_log('sending reply u:' . $username . ' to:' . $sms_to . ' m:[' . $message . '] smsc:[' . $smsc . ']', 3, 'sms_subscribe_handle');
			sendsms_helper($username, $sms_to, $message, 'text', '', $smsc, TRUE);
		}
	}
	return $ok;
}

/*
 * intercept incoming sms and look for keyword BC followed by subscribe keyword this feature will do BC but for subscribe keyword sms format: BC <sms_subscribe_keyword> <message> #<sms_subscribe_keyword> <message> @param $sms_datetime incoming SMS date/time @param $sms_sender incoming SMS sender @message incoming SMS message before intercepted @param $sms_receiver receiver number that is receiving incoming SMS @return array $ret
 */
function sms_subscribe_hook_recvsms_intercept($sms_datetime, $sms_sender, $message, $sms_receiver) {
	$msg = explode(" ", $message);
	$bc = strtoupper($msg[0]);
	$keyword = '';
	$message = '';
	if ($bc == 'BC') {
		$keyword = strtoupper($msg[1]);
		for ($i = 2; $i < count($msg); $i++) {
			$message .= $msg[$i] . ' ';
		}
	} else if (substr($bc, 0, 1) == '#') {
		$keyword = str_replace('#', '', $bc);
		for ($i = 1; $i < count($msg); $i++) {
			$message .= $msg[$i] . ' ';
		}
	}
	$keyword = trim($keyword);
	$message = trim($message);
	$hooked = false;
	if ($keyword && $message) {
		_log("recvsms_intercept k:" . $keyword . " m:" . $message, 1, "sms_subscribe");
		
		// if not available then the keyword is exists
		if (!sms_subscribe_hook_checkavailablekeyword($keyword)) {
			$c_uid = user_mobile2uid($sms_sender);
			$c_username = user_uid2username($c_uid);
			if ($c_uid && $c_username) {
				$list = dba_search(_DB_PREF_ . '_featureSubscribe', 'subscribe_id, forward_param', array(
					'uid' => $c_uid,
					'subscribe_keyword' => $keyword 
				));
				if ($list[0]['subscribe_id']) {
					$forward_param = ($list[0]['forward_param'] ? $list[0]['forward_param'] : 'BC');
					$sms_datetime = core_display_datetime($sms_datetime);
					_log("recvsms_intercept dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " uid:" . $c_uid . " username:" . $c_username . " bc:" . $bc . " keyword:" . $keyword . " message:" . $message . " fwd:" . $forward_param, 3, "sms_subscribe");
					$hooked = true;
				}
			}
		}
	}
	$ret = array();
	if ($hooked) {
		$ret['modified'] = true;
		$ret['hooked'] = true;
		$ret['param']['message'] = $keyword . ' ' . $forward_param . ' ' . $message;
	}
	return $ret;
}

function sms_subscribe_hook_playsmsd() {
	global $core_config;
	
	// fetch hourly
	if (!core_playsmsd_timer(3600)) {
		return;
	}
	
	$db_table = _DB_PREF_ . "_featureSubscribe";
	$conditions = array(
		'subscribe_enable' => 1 
	);
	$extras = array(
		'AND duration' => '>0' 
	);
	$list_subscribe = dba_search($db_table, '*', $conditions, '', $extras);
	foreach ($list_subscribe as $subscribe) {
		$c_id = $subscribe['subscribe_id'];
		$c_duration = $subscribe['duration'];
		$date_now = new DateTime(core_get_datetime());
		$list_member = dba_search(_DB_PREF_ . '_featureSubscribe_member', '*', array(
			'subscribe_id' => $c_id 
		));
		foreach ($list_member as $member) {
			$is_expired = FALSE;
			$date_since = new DateTime($member['member_since']);
			$diff = $date_since->diff($date_now);
			$d = (int) $diff->format('%R%a');
			// _log('check duration:' . $d . ' day set duration:' . $c_duration . ' date_now:' . core_get_datetime() . ' date_since:' . $member['member_since'] . ' k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, 'sms_subscribe_hook_playsmsd');
			if ($c_duration > 1000) {
				// days
				$c_interval = $c_duration - 1000;
				if ($c_interval && $d && ($d >= $c_interval)) {
					_log('expired duration:' . $d . ' day k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, 'sms_subscribe_hook_playsmsd');
					$is_expired = TRUE;
				}
			} else if ($c_duration > 100) {
				// weeks
				$c_interval = $c_duration - 100;
				$w = floor($d / 7);
				// _log('interval:' . $c_interval . ' d:' . $d . ' w:' . $w, 3, 'sms_subscribe_hook_playsmsd');
				if ($c_interval && $w && ($w >= $c_interval)) {
					_log('expired duration:' . $w . ' week k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, 'sms_subscribe_hook_playsmsd');
					$is_expired = TRUE;
				}
			} else if ($c_duration > 0) {
				// months
				$c_interval = $c_duration;
				$m = floor($d / 30);
				// _log('interval:' . $c_interval . ' d:' . $d . ' m:' . $m, 3, 'sms_subscribe_hook_playsmsd');
				if ($c_interval && $m && ($m >= $c_interval)) {
					_log('expired duration:' . $m . ' month k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, 'sms_subscribe_hook_playsmsd');
					$is_expired = TRUE;
				}
			}
			if ($is_expired) {
				_sms_subscribe_member_expired($subscribe, $member);
			}
		}
	}
}

function _sms_subscribe_member_expired($subscribe, $member) {
	$c_username = user_uid2username($subscribe['uid']);
	if ($c_username && $member['member_id']) {
		if (sms_subscribe_member_remove($member['member_id'])) {
			_log('removed k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, '_sms_subscribe_member_expired');
			if ($subscribe['expire_msg']) {
				_log('SMS k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'] . ' message:[' . $subscribe['expire_msg'] . ']', 3, '_sms_subscribe_member_expired');
				sendsms_helper($c_username, $member['member_number'], $subscribe['expire_msg'], 'text', '', $subscribe['smsc'], TRUE);
			}
		}
	}
}

function sms_subscribe_member_remove($member_id) {
	$db_table = _DB_PREF_ . "_featureSubscribe_member";
	$conditions = array(
		'member_id' => $member_id 
	);
	return dba_remove($db_table, $conditions);
}
