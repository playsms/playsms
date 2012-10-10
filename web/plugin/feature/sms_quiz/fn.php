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
	$db_query = "SELECT uid FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_keyword='$quiz_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$c_uid = $db_row['uid'];
		if (sms_quiz_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $quiz_keyword, $quiz_param, $raw_message)) {
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

function sms_quiz_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $quiz_keyword, $quiz_param = '', $raw_message = '') {
	global $core_config, $datetime_now;
	$ok = false;
	$username = uid2username($c_uid);
	$sms_to = $sms_sender; // we are replying to this sender
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_keyword='$quiz_keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($db_row['quiz_enable'] == 1) {
		if ($db_row['quiz_answer'] == strtoupper($quiz_param)) {
			$message = $db_row['quiz_msg_correct'];
		} else {
			$message = $db_row['quiz_msg_incorrect'];
		}
		$quiz_id = $db_row['quiz_id'];
		$answer = strtoupper($quiz_param);
		$db_query = "INSERT INTO " . _DB_PREF_ . "_featureQuiz_log (quiz_id,quiz_answer,quiz_sender,in_datetime) VALUES ('$quiz_id','$answer','$sms_to','$datetime_now')";
		if ($logged = @dba_insert_id($db_query)) {
			//list($ok,$to,$smslog_id,$queue) = sendsms_pv($username, $sms_to, $message);
			$unicode = 0;
			if (function_exists('mb_detect_encoding')) {
				$encoding = mb_detect_encoding($message, 'auto');
				if ($encoding != 'ASCII') {
					$unicode = 1;
				}
			}
			list($ok, $to, $smslog_id, $queue) = sendsms_pv($username, $sms_to, $message, 'text', $unicode);
                        $ok = $ok[0];
		}
	} else if ($db_row['quiz_keyword'] == $quiz_keyword) {
		// returns true even if its logged as correct/incorrect answer
		// this situation happens when user answers a disabled quiz
		// returning false will make this SMS as unhandled SMS
		$ok = true;
	}
	return $ok;
}

?>