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
 * Implementations of hook keyword_isavail()
 * 
 * @param $keyword SMS keyword
 * @return bool true if keyword is available, false if already registered in database
 */
function sms_subscribe_hook_keyword_isavail($keyword)
{
	$keyword = strtoupper(core_sanitize_alphanumeric($keyword));

	$db_query = "SELECT subscribe_id FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_keyword=?";
	if (dba_num_rows($db_query, [$keyword])) {

		return false;
	}

	return true;
}

/**
 * Implementations of hook recvsms_process()
 * 
 * @param string $sms_datetime date and time when incoming sms inserted to playsms
 * @param string $sms_sender sender on incoming sms
 * @param string $subscribe_keyword check if keyword is for sms_board
 * @param string $subscribe_param get parameters from incoming sms
 * @param string $sms_receiver receiver number that is receiving incoming sms
 * @param string $smsc SMSC
 * @param string $raw_message Original SMS
 * @return array array of keyword owner uid and status, true if incoming sms handled
 */
function sms_subscribe_hook_recvsms_process($sms_datetime, $sms_sender, $subscribe_keyword, $subscribe_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	$ret = [];

	$uid = 0;
	$status = false;

	$subscribe_keyword = strtoupper(core_sanitize_alphanumeric($subscribe_keyword));
	$subscribe_param = trim($subscribe_param);

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_keyword=?";
	$db_result = dba_query($db_query, [$subscribe_keyword]);
	if ($db_row = dba_fetch_array($db_result)) {
		if ($uid = $db_row['uid'] && $db_row['subscribe_enable']) {
			_log('begin k:' . $subscribe_keyword . ' p:' . $subscribe_param, 2, 'sms_subscribe');
			if (sms_subscribe_handle($db_row, $sms_datetime, $sms_sender, $subscribe_keyword, $subscribe_param, $sms_receiver, $smsc, $raw_message)) {
				$status = true;
			}
			$status_text = ($status ? 'handled' : 'unhandled');
			_log('end k:' . $subscribe_keyword . ' p:' . $subscribe_param . ' s:' . $status_text, 2, 'sms_subscribe');
		}
	}

	$ret['uid'] = $uid;
	$ret['status'] = $status;

	return $ret;
}

function sms_subscribe_handle($list, $sms_datetime, $sms_sender, $subscribe_keyword, $subscribe_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	global $core_config;

	$c_uid = $list['uid'];
	$username = user_uid2username($c_uid);
	$subscribe_param = preg_replace('/\s+/', ' ', $subscribe_param);
	_log("username:" . $username . " sender:" . $sms_sender . " keyword:" . $subscribe_keyword . " param:" . $subscribe_param, 3, "sms_subscribe");

	$subscribe_accept_param = strtoupper(core_sanitize_alphanumeric($list['subscribe_param']));
	$subscribe_reject_param = strtoupper(core_sanitize_alphanumeric($list['unsubscribe_param']));
	$forward_param = strtoupper(core_sanitize_alphanumeric($list['forward_param']));
	$smsc = gateway_decide_smsc($smsc, $list['smsc']);

	// check for BC sub-keyword
	$subscribe_id = $list['subscribe_id'];

	// check for BC/forward param	
	$subscribe_param_array = explode(' ', $subscribe_param, 2);
	$bc = strtoupper(core_sanitize_alphanumeric($subscribe_param_array[0]));
	if ((($bc == 'BC') || ($forward_param && ($bc == $forward_param))) && ($c_uid == user_mobile2uid($sms_sender))) {
		$message = isset($subscribe_param_array[1]) ? $subscribe_param_array[1] : '';
		if (!$message) {

			return false;
		}

		$db_query = "SELECT member_number FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id=?";
		$db_result = dba_query($db_query, [$subscribe_id]);
		$bc_to = [];
		while ($db_row = dba_fetch_array($db_result)) {
			$bc_to[] = $db_row['member_number'];
		}
		if (is_array($bc_to) && count($bc_to) > 0) {
			$unicode = core_detect_unicode($message);
			_log('BC sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' count:' . count($bc_to) . ' m:' . $message, 3, "sms_subscribe");
			$message = trim($message);
			list($ok, $to, $smslog_id, $queue) = sendsms_helper($username, $bc_to, $message, 'text', $unicode, $smsc, true);

			return true;
		} else {

			return false;
		}
	}

	// no sender
	if (!($sms_to = $sms_sender)) {

		return false;
	}

	// check for subscribe/unsubscribe sub-keyword
	$subscribe_param = strtoupper(core_sanitize_alphanumeric($subscribe_param));
	$msg1 = trim($list['subscribe_msg']);
	$msg2 = trim($list['unsubscribe_msg']);
	$unknown_format_msg = trim($list['unknown_format_msg']);
	$already_member_msg = trim($list['already_member_msg']);
	$message = "";

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE member_number=? AND subscribe_id=?";
	if (dba_num_rows($db_query, [$sms_to, $subscribe_id])) {
		switch ($subscribe_param) {
			case "OFF":
			case "OUT":
			case "UNREG":
			case $subscribe_reject_param:
				$message = $msg2;
				$success = 'FAILED';
				$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE member_number=? AND subscribe_id=?";
				if (dba_affected_rows($db_query, [$sms_to, $subscribe_id])) {
					$success = 'SUCCESS';
				}
				_log('UNREG ' . $success . ' sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to . ' m:[' . $message . ']', 2, "sms_subscribe");
				break;

			case "ON":
			case "IN":
			case "REG":
			case $subscribe_accept_param:
				$message = $already_member_msg;
				_log('REG FAILED already a member sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to . ' m:[' . $message . ']', 2, "sms_subscribe");
				break;

			default:
				$message = $unknown_format_msg;
				_log('Unknown format sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to, 2, "sms_subscribe");
				break;
		}
	} else {
		switch ($subscribe_param) {
			case "ON":
			case "IN":
			case "REG":
			case $subscribe_accept_param:
				$message = $msg1;
				$success = 'FAILED';
				$db_query = "INSERT INTO " . _DB_PREF_ . "_featureSubscribe_member (subscribe_id,member_number,member_since) VALUES (?,?,?)";
				if (dba_insert_id($db_query, [$subscribe_id, $sms_to, core_get_datetime()])) {
					$success = 'SUCCESS';
				}
				_log('REG ' . $success . ' sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to . ' m:[' . $message . ']', 2, "sms_subscribe");
				break;

			default:
				$message = $unknown_format_msg;
				_log('Unknown format sender:' . $sms_sender . ' keyword:' . $subscribe_keyword . ' mobile:' . $sms_to, 2, "sms_subscribe");
				$ok = true;
				break;
		}

	}

	if ($message) {
		$unicode = core_detect_unicode($message);
		$message = trim($message);
		_log('sending reply u:' . $username . ' to:' . $sms_to . ' m:[' . $message . '] smsc:[' . $smsc . ']', 3, 'sms_subscribe_handle');
		sendsms_helper($username, $sms_to, $message, 'text', $unicode, $smsc, true);
	}

	return true;
}

/**
 * Intercept incoming sms and look for keyword BC followed by subscribe keyword
 * This feature will do BC but for subscribe keyword
 * SMS format: BC <sms_subscribe_keyword> <message>
 * SMS format: #<sms_subscribe_keyword> <message>
 * 
 * @param string $sms_datetime incoming SMS date/time
 * @param string $sms_sender incoming SMS sender
 * @param string $message incoming SMS message before intercepted
 * @param string $sms_receiver receiver number that is receiving incoming SMS
 * @param string $smsc SMSC
 * @return array
 */
function sms_subscribe_hook_recvsms_process_before($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc)
{
	if (!trim($message)) {

		return [];
	}

	$raw_message = $message;
	$msg = explode(" ", $message);
	$bc = strtoupper($msg[0]);
	$keyword = '';
	$message = '';
	if ($bc == 'BC') {
		$keyword = strtoupper(core_sanitize_alphanumeric($msg[1]));
		for ($i = 2; $i < count($msg); $i++) {
			$message .= $msg[$i] . ' ';
		}
	} else if (substr($bc, 0, 1) == '#') {
		$keyword = core_sanitize_alphanumeric($bc);
		for ($i = 1; $i < count($msg); $i++) {
			$message .= $msg[$i] . ' ';
		}
	}
	$keyword = trim($keyword);
	$message = trim($message);

	$forward_param = '';
	if ($keyword && $message) {
		$c_uid = user_mobile2uid($sms_sender);

		_log("recvsms_process_before uid:" . $c_uid . " k:" . $keyword . " m:" . $message, 1, "sms_subscribe");

		// if not available then the keyword is exists
		if (!sms_subscribe_hook_keyword_isavail($keyword)) {
			$c_username = user_uid2username($c_uid);
			if ($c_uid && $c_username) {
				$list = dba_search(_DB_PREF_ . '_featureSubscribe', 'subscribe_id, forward_param', [
					'uid' => $c_uid,
					'subscribe_keyword' => $keyword
				]);
				if (isset($list[0]['subscribe_id']) && (int) $list[0]['subscribe_id']) {
					$forward_param = isset($list[0]['forward_param']) ? $list[0]['forward_param'] : 'BC';
					$sms_datetime = core_display_datetime($sms_datetime);
					_log("recvsms_process_before dt:" . $sms_datetime . " s:" . $sms_sender . " r:" . $sms_receiver . " uid:" . $c_uid . " username:" . $c_username . " bc:" . $bc . " keyword:" . $keyword . " message:" . $message . " fwd:" . $forward_param, 3, "sms_subscribe");

					/**
					 * Modified incoming SMS message from below format before entering recvsms_process():
					 * BC SUBS_KEYWORD MESSAGE or #SUBS_KEYWORD MESSAGE to SUBS_KEYWORD SUBS_FWD_KEYWORD MESSAGE
					 */
					return [
						'modified' => true,
						'hooked' => true,
						'param' => [
							'message' => $keyword . ' ' . $forward_param . ' ' . $message,
						]
					];
				}
			}
		}
	}

	return [];
}

function sms_subscribe_hook_playsmsd()
{
	global $core_config;

	// fetch hourly
	if (!core_playsmsd_timer(3600)) {
		return;
	}

	$db_table = _DB_PREF_ . "_featureSubscribe";
	$conditions = [
		'subscribe_enable' => 1
	];
	$extras = [
		'AND duration' => '>0'
	];
	$list_subscribe = dba_search($db_table, '*', $conditions, [], $extras);
	foreach ( $list_subscribe as $subscribe ) {
		$c_id = $subscribe['subscribe_id'];
		$c_duration = $subscribe['duration'];
		$date_now = new DateTime(core_get_datetime());
		$list_member = dba_search(_DB_PREF_ . '_featureSubscribe_member', '*', ['subscribe_id' => $c_id]);
		foreach ( $list_member as $member ) {
			$is_expired = false;
			$date_since = new DateTime($member['member_since']);
			$diff = $date_since->diff($date_now);
			$d = (int) $diff->format('%R%a');
			// _log('check duration:' . $d . ' day set duration:' . $c_duration . ' date_now:' . core_get_datetime() . ' date_since:' . $member['member_since'] . ' k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, 'sms_subscribe_hook_playsmsd');
			if ($c_duration > 1000) {
				// days
				$c_interval = $c_duration - 1000;
				if ($c_interval && $d && ($d >= $c_interval)) {
					_log('expired duration:' . $d . ' day k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, 'sms_subscribe_hook_playsmsd');
					$is_expired = true;
				}
			} else if ($c_duration > 100) {
				// weeks
				$c_interval = $c_duration - 100;
				$w = floor($d / 7);
				// _log('interval:' . $c_interval . ' d:' . $d . ' w:' . $w, 3, 'sms_subscribe_hook_playsmsd');
				if ($c_interval && $w && ($w >= $c_interval)) {
					_log('expired duration:' . $w . ' week k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, 'sms_subscribe_hook_playsmsd');
					$is_expired = true;
				}
			} else if ($c_duration > 0) {
				// months
				$c_interval = $c_duration;
				$m = floor($d / 30);
				// _log('interval:' . $c_interval . ' d:' . $d . ' m:' . $m, 3, 'sms_subscribe_hook_playsmsd');
				if ($c_interval && $m && ($m >= $c_interval)) {
					_log('expired duration:' . $m . ' month k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, 'sms_subscribe_hook_playsmsd');
					$is_expired = true;
				}
			}
			if ($is_expired) {
				_sms_subscribe_member_expired($subscribe, $member);
			}
		}
	}
}

function _sms_subscribe_member_expired($subscribe, $member)
{
	$c_username = user_uid2username($subscribe['uid']);
	if ($c_username && $member['member_id']) {
		if (sms_subscribe_member_remove($member['member_id'])) {
			_log('removed k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'], 3, '_sms_subscribe_member_expired');
			if ($subscribe['expire_msg']) {
				$unicode = core_detect_unicode($subscribe['expire_msg']);
				_log('SMS k:' . $subscribe['subscribe_keyword'] . ' member_id:' . $member['member_id'] . ' number:' . $member['member_number'] . ' message:[' . $subscribe['expire_msg'] . ']', 3, '_sms_subscribe_member_expired');
				sendsms_helper($c_username, $member['member_number'], $subscribe['expire_msg'], 'text', $unicode, $subscribe['smsc'], true);
			}
		}
	}
}

function sms_subscribe_member_remove($member_id)
{
	$db_table = _DB_PREF_ . "_featureSubscribe_member";
	$conditions = [
		'member_id' => $member_id
	];
	return dba_remove($db_table, $conditions);
}

/**
 * Check for valid ID
 * 
 * @param int $id
 * @return bool
 */
function sms_subscribe_check_id($id)
{
	return core_check_id($id, _DB_PREF_ . '_featureSubscribe', 'subscribe_id');
}

/**
 * Get subscribe keyword by ID
 * 
 * @param int $id
 * @return string
 */
function sms_subscribe_get_keyword($id)
{
	$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id=?";
	$db_result = dba_query($db_query, [$id]);
	$db_row = dba_fetch_array($db_result);
	$db_row = _display($db_row);

	return isset($db_row['subscribe_keyword']) ? $db_row['subscribe_keyword'] : '';

}