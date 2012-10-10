<?php
defined('_SECURE_') or die('Forbidden');

/*
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword
 *   checkavailablekeyword() will insert keyword for checking to the hook here
 * @return
 *   TRUE if keyword is available
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
 * Implementations of hook setsmsincomingaction()
 *
 * @param $sms_datetime
 *   date and time when incoming sms inserted to playsms
 * @param $sms_sender
 *   sender on incoming sms
 * @param $subscribe_keyword
 *   check if keyword is for sms_subscribe
 * @param $subscribe_param
 *   get parameters from incoming sms
 * @param $sms_receiver
 *   receiver number that is receiving incoming sms
 * @return $ret
 *   array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_subscribe_hook_setsmsincomingaction($sms_datetime, $sms_sender, $subscribe_keyword, $subscribe_param = '', $sms_receiver = '', $raw_message = '') {
	$ok = false;
	$db_query = "SELECT uid FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_keyword='$subscribe_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$c_uid = $db_row['uid'];
		if (sms_subscribe_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $subscribe_keyword, $subscribe_param, $raw_message)) {
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

function sms_subscribe_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $subscribe_keyword, $subscribe_param = '', $raw_message = '') {
	global $core_config;
	$ok = false;
	$subscribe_param = strtoupper($subscribe_param);
	$subscribe_keyword = strtoupper($subscribe_keyword);
	$username = uid2username($c_uid);
	$sms_to = $sms_sender; // we are replying to this sender
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_keyword='$subscribe_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		if (! $db_row['subscribe_enable']) {
			$message = _('Subscribe service inactive');
			//list($ok,$to,$smslog_id,$queue) = sendsms_pv($username, $sms_to, $message);
			//$ok = $ok[0];
			$unicode = 0;
			if (function_exists('mb_detect_encoding')) {
				$encoding = mb_detect_encoding($message, 'auto');
				if ($encoding != 'ASCII') {
					$unicode = 1;
				}
			}
			list($ok, $to, $smslog_id, $queue) = sendsms_pv($username, $sms_to, $message, 'text', $unicode);
			return $ok[0];
		}
	}
	$c_uid = $db_row['uid'];
	$subscribe_id = $db_row['subscribe_id'];
	$num_rows = dba_num_rows($db_query);
	if ($num_rows) {
		$msg1 = $db_row['subscribe_msg'];
		$msg2 = $db_row['unsubscribe_msg'];

		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE member_number='$sms_to' AND subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$num_rows = dba_num_rows($db_query);
		if ($num_rows == 0) {
			$member = false;
			$db_query = "INSERT INTO " . _DB_PREF_ . "_featureSubscribe_member (subscribe_id,member_number,member_since) VALUES ('$subscribe_id','$sms_to','".$core_config['datetime']['now']."')";
			switch ($subscribe_param) {
				case "ON" :
					$message = $msg1;
					$logged = dba_query($db_query);
					$ok = true;
					break;

				case "IN" :
					$message = $msg1;
					$logged = dba_query($db_query);
					$ok = true;
					break;

				case "REG" :
					$message = $msg1;
					$logged = dba_query($db_query);
					$ok = true;
					break;

				case "OFF" :
					$message = _('You are not a member');
					$ok = true;
					break;

				case "OUT" :
					$message = _('You are not a member');
					$ok = true;
					break;

				case "UNREG" :
					$message = _('You are not a member');
					$ok = true;
					break;

				default :
					$message = _('Unknown SMS format');
					$ok = true;
					break;
			}
		} else {
			$member = true;
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE member_number='$sms_to' AND subscribe_id='$subscribe_id'";
			switch ($subscribe_param) {
				case "OFF" :
					$message = $msg2;
					$deleted = dba_query($db_query);
					if ($deleted) {
						$ok = true;
					}
					break;

				case "OUT" :
					$message = $msg2;
					$deleted = dba_query($db_query);
					if ($deleted) {
						$ok = true;
					}
					break;

				case "UNREG" :
					$message = $msg2;
					$deleted = dba_query($db_query);
					if ($deleted) {
						$ok = true;
					}
					break;

				case "ON" :
					$message = _('You already a member');
					$ok = true;
					break;

				case "IN" :
					$message = _('You already a member');
					$ok = true;
					break;

				case "REG" :
					$message = _('You already a member');
					$ok = true;
					break;

				default :
					$message = _('Unknown sms format');
					$ok = true;
					break;
			}
		}
		list($ok,$to,$smslog_id,$queue) = sendsms_pv($username, $sms_to, $message);
		$ok = $ok[0];
	} else {
		$ok = false;
	}
	return $ok;
}
?>
