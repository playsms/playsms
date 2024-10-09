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
function sms_quiz_hook_keyword_isavail($keyword)
{
	$keyword = strtoupper(core_sanitize_alphanumeric($keyword));

	$db_query = "SELECT quiz_id FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_keyword=?";
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
 * @param string $quiz_keyword check if keyword is for sms_board
 * @param string $quiz_param get parameters from incoming sms
 * @param string $sms_receiver receiver number that is receiving incoming sms
 * @param string $smsc SMSC
 * @param string $raw_message Original SMS
 * @return array array of keyword owner uid and status, true if incoming sms handled
 */
function sms_quiz_hook_recvsms_process($sms_datetime, $sms_sender, $quiz_keyword, $quiz_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	$ret = [];

	$uid = 0;
	$status = false;

	$quiz_keyword = strtoupper(core_sanitize_alphanumeric($quiz_keyword));
	$quiz_param = trim($quiz_param);

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_keyword=?";
	$db_result = dba_query($db_query, [$quiz_keyword]);
	if ($db_row = dba_fetch_array($db_result)) {
		if ($uid = $db_row['uid'] && $db_row['quiz_enable']) {
			$smsc = gateway_decide_smsc($smsc, $db_row['smsc']);
			_log('begin k:' . $quiz_keyword . ' c:' . $quiz_param, 2, 'sms_quiz');
			if (sms_quiz_handle($db_row, $sms_datetime, $sms_sender, $quiz_keyword, $quiz_param, $sms_receiver, $smsc, $raw_message)) {
				$status = true;
			}
			$status_text = ($status ? 'handled' : 'unhandled');
			_log('end k:' . $quiz_keyword . ' c:' . $quiz_param . ' s:' . $status_text, 2, 'sms_quiz');
		}
	}
	$ret['uid'] = $uid;
	$ret['status'] = $status;

	return $ret;
}

function sms_quiz_handle($list, $sms_datetime, $sms_sender, $quiz_keyword, $quiz_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	global $core_config;

	if (($list['quiz_enable']) && $quiz_param) {
		if (strtoupper($list['quiz_answer']) == strtoupper($quiz_param)) {
			$message = $list['quiz_msg_correct'];
		} else {
			$message = $list['quiz_msg_incorrect'];
		}
		$db_query = "INSERT INTO " . _DB_PREF_ . "_featureQuiz_log (quiz_id,quiz_answer,quiz_sender,in_datetime) VALUES (?,?,?,?)";
		if (dba_insert_id($db_query, [$list['quiz_id'], $quiz_param, $sms_sender, core_get_datetime()])) {
			if ($message && ($username = user_uid2username($list['uid']))) {
				$unicode = core_detect_unicode($message);
				$message = addslashes($message);
				list($ok, $to, $smslog_id, $queue) = sendsms_helper($username, $sms_sender, $message, 'text', $unicode, $smsc);
			}

			return true;
		}
	}

	return false;
}

/**
 * Check for valid ID
 * 
 * @param int $id
 * @return bool
 */
function sms_quiz_check_id($id)
{
	return core_check_id($id, _DB_PREF_ . '_featureQuiz', 'quiz_id');
}