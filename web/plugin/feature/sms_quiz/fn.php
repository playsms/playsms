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
function sms_quiz_hook_checkavailablekeyword($keyword) {
	$ok = true;
	$db_query = "SELECT quiz_id FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_keyword='$keyword'";
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
 * @param $quiz_keyword
 *   check if keyword is for sms_quiz
 * @param $quiz_param
 *   get parameters from incoming sms
 * @param $sms_receiver
 *   receiver number that is receiving incoming sms
 * @return $ret
 *   array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_quiz_hook_setsmsincomingaction($sms_datetime, $sms_sender, $quiz_keyword, $quiz_param = '', $sms_receiver = '', $raw_message = '') {
	$ok = false;
	$db_query = "SELECT * FROM "._DB_PREF_."_featureQuiz WHERE quiz_keyword='$quiz_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		if ($db_row['uid'] && $db_row['quiz_enable']) {
			logger_print('begin k:'.$quiz_keyword.' c:'.$quiz_param, 2, 'sms_quiz');
			if (sms_quiz_handle($db_row,$sms_datetime,$sms_sender,$quiz_keyword,$quiz_param,$sms_receiver,$raw_message)) {
				$ok = true;
			}
			$status = ( $ok ? 'handled' : 'unhandled' );
			logger_print('end k:'.$quiz_keyword.' c:'.$quiz_param.' s:'.$status, 2, 'sms_quiz');
		}
	}
	$ret['uid'] = $db_row['uid'];
	$ret['status'] = $ok;
	return $ret;
}

function sms_quiz_handle($list, $sms_datetime, $sms_sender, $quiz_keyword, $quiz_param = '', $sms_receiver='', $raw_message = '') {
	global $core_config;
	$ok = false;
	$sms_to = $sms_sender; // we are replying to this sender
	$quiz_keyword = strtoupper(trim($quiz_keyword));
	$quiz_param = strtoupper(trim($quiz_param));
	if (($quiz_enable = $list['quiz_enable']) && $quiz_param) {
		if (strtoupper($list['quiz_answer']) == $quiz_param) {
			$message = $list['quiz_msg_correct'];
		} else {
			$message = $list['quiz_msg_incorrect'];
		}
		$quiz_id = $list['quiz_id'];
		$answer = strtoupper($quiz_param);
		$db_query = "INSERT INTO " . _DB_PREF_ . "_featureQuiz_log (quiz_id,quiz_answer,quiz_sender,in_datetime) VALUES ('$quiz_id','$answer','$sms_to','".core_get_datetime()."')";
		if ($logged = @dba_insert_id($db_query)) {
			if ($message && ($username = uid2username($list['uid']))) {
				$unicode = core_detect_unicode($message);
				list($ok, $to, $smslog_id, $queue) = sendsms($username, $sms_to, $message, 'text', $unicode);
			}
			$ok = true;
		}
	}
	return $ok;
}

?>