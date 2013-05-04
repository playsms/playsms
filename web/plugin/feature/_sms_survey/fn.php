<?php
defined('_SECURE_') or die('Forbidden');

/* Implementations of hook playsmsd()
 *
 */
function sms_survey_hook_playsmsd() {
	global $core_config;
	// get enabled and started survey, but not running yet
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey WHERE deleted='0' AND status='1' AND started='1' AND running='0'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$c_sid = $db_row['id'];
		// set survey as running survey
		$session = md5($c_sid.mktime());
		$db_query1 = "UPDATE "._DB_PREF_."_featureSurvey SET session='$session',running='1' WHERE id='$c_sid'";
		if ($db_result1 = dba_affected_rows($db_query1)) {
			// get current survey data
			$s = sms_survey_getdatabyid($c_sid);
			$c_uid = $s['uid'];
			$c_username = uid2username($c_uid);
			$c_keyword = $s['keyword'];
			// get current survey questions, focus only on first question, index 0
			$q = sms_survey_getquestions($c_sid);
			$c_message = $q[0]['question'];
			$c_sms_msg = $c_keyword." ".$c_message;
			// get current survey members
			$m = sms_survey_getmembers($c_sid);
			for ($i=0;$i<count($m);$i++) {
				// if member's mobile exists
				if ($c_sms_to = $m[$i]['mobile']) {
					logger_print("playsmsd send start qn:1 sid:".$c_sid." username:".$c_username." to:".$c_sms_to." msg:".$c_sms_msg, 3, "sms_survey");
					// if member's mobile, question and username owned the survey exists
					if ($c_sms_to && $c_sms_msg && $c_username) {
						$type = 'text';
						// $unicode = '0';
						// send message to member
						// list($ok,$to,$smslog_id,$queue) = sendsms($c_username,$c_sms_to,$c_sms_msg,$type,$unicode);
						$unicode = core_detect_unicode($message);
						list($ok, $to, $smslog_id, $queue) = sendsms($c_username, $c_sms_to, $c_sms_msg, 'text', $unicode);
						$ok[0] = $ok[0] ? "true" : "false" ;
						logger_print("playsmsd send finish sid:".$c_sid." smslog_id:".$smslog_id[0]." ok:".$ok[0], 2, "sms_survey");
						// save the log
						$log = "";
						$log['survey_id'] = $c_sid;
						$log['question_id'] = $q[0]['id'];
						$log['member_id'] = $m[$i]['id'];
						$log['link_id'] = sms_survey_getlinkid($session, $c_sid, $m[$i]['id']);
						$log['smslog_id'] = $smslog_id[0];
						$log['name'] = $m[$i]['name'];
						$log['mobile'] = $m[$i]['mobile'];
						$log['question'] = $q[0]['question'];
						$log['question_number'] = 1;
						$log['creation_datetime'] = $core_config['datetime']['now'];
						$log['session'] = $session;
						sms_survey_savelog($log);
					}
				}
			}
		}
		// set survey as completed
		$db_query2 = "UPDATE "._DB_PREF_."_featureSurvey SET status='1',started='0',running='2' WHERE id='$c_sid'";
		$db_result2 = dba_affected_rows($db_query2);
	}
}

/*
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword
 *   checkavailablekeyword() will insert keyword for checking to the hook here
 * @return
 *   TRUE if keyword is available
 */
function sms_survey_hook_checkavailablekeyword($keyword) {
	$ok = true;
	$db_query = "SELECT id FROM " . _DB_PREF_ . "_featureSurvey WHERE keyword='$keyword'";
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
 * @param $survey_keyword
 *   check if keyword is for sms_survey
 * @param $survey_param
 *   get parameters from incoming sms
 * @param $sms_receiver
 *   receiver number that is receiving incoming sms
 * @return $ret
 *   array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_survey_hook_setsmsincomingaction($sms_datetime, $sms_sender, $survey_keyword, $survey_param = '', $sms_receiver = '', $raw_message = '') {
	$ok = false;
	$db_query = "SELECT uid FROM " . _DB_PREF_ . "_featureSurvey WHERE keyword='$survey_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$c_uid = $db_row['uid'];
		if (sms_survey_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $survey_keyword, $survey_param, $raw_message)) {
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

// handle survey
function sms_survey_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $survey_keyword, $survey_param = '', $raw_message = '') {
	global $core_config;
	$ok = false;
	$survey_keyword = strtoupper(trim($survey_keyword));
	$survey_param = strtoupper(trim($survey_param));
	// get survey data by keyword
	$data = sms_survey_getdatabykeyword($survey_keyword);
	if ($data['status'] == 1) {
		// survey enabled, accept incoming answers
		$session = $data['session'];
		$sid = $data['id'];
		$m = sms_survey_getmemberbymobile($sid, $sms_sender);
		// link_id is used to link each questions and answers
		if ($link_id = sms_survey_getlinkid($session, $sid, $m['id'])) {
			// get last question data from log
			$l = sms_survey_getoutlogs($link_id);
			$outlogs = $l[count($l) - 1];
			$qn = $outlogs['question_number'];
			$next_qn = $qn + 1;
			// save answer in log
			$log = "";
			$log['incoming'] = 1;
			$log['question_number'] = $qn;
			$log['link_id'] = $link_id;
			$log['in_datetime'] = $sms_datetime;
			$log['in_sender'] = $sms_sender;
			$log['in_receiver'] = $sms_receiver;
			$log['answer'] = $survey_param;
			$log['session'] = $session;
			if (sms_survey_savelog($log)) {
				// get next question
				$q = sms_survey_getquestions($sid);
				// stop when ran out questions
				if ($qn < count($q)) {
					$c_message = $q[$qn]['question']; // yes, not $next_qn, array questions start from 0
					$c_username = uid2username($c_uid);
					$c_keyword = $survey_keyword;
					$c_sms_msg = $c_keyword." ".$c_message;
					$c_sms_to = $sms_sender;
					logger_print("playsmsd send start next qn:".$next_qn." sid:".$c_sid." username:".$c_username." to:".$c_sms_to." msg:".$c_sms_msg, 3, "sms_survey");
					// if member's mobile, question and username owned the survey exists
					if ($c_sms_to && $c_sms_msg && $c_username) {
						$type = 'text';
						$unicode = '0';
						// send next question to member
						list($ok,$to,$smslog_id,$queue) = sendsms($c_username,$c_sms_to,$c_sms_msg,$type,$unicode);
						$ok[0] = $ok[0] ? "true" : "false" ;
						logger_print("playsmsd send finish sid:".$c_sid." smslog_id:".$smslog_id[0]." ok:".$ok[0], 2, "sms_survey");
						// save the log
						$log = "";
						$log['survey_id'] = $sid;
						$log['question_id'] = $q[$qn]['id'];
						$log['member_id'] = $m['id'];
						$log['link_id'] = $link_id;
						$log['smslog_id'] = $smslog_id[0];
						$log['name'] = $m['name'];
						$log['mobile'] = $m['mobile'];
						$log['question'] = $q[$qn]['question'];
						$log['question_number'] = $next_qn;
						$log['creation_datetime'] = $core_config['datetime']['now'];
						$log['session'] = $session;
						sms_survey_savelog($log);
					}
				}
				// set handled
				$ok = true;
			}
		}
	} else {
		// returns true even if its not handled since survey is disabled
		// returning false will make this SMS as unhandled SMS
		$ok = true;
	}
	return $ok;
}

// get link id
function sms_survey_getlinkid($session, $sid, $member_id) {
	$link_id = $session.'.'.$sid.'.'.$member_id;
	return $link_id;
}

// get logs for outgoing
function sms_survey_getoutlogs($link_id) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey_log WHERE link_id='$link_id' AND incoming='0' ORDER BY id";
	$db_result = dba_query($db_query);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[$i] = $db_row;
		$i++;
	}
	return $ret;
}

// get logs for incoming
function sms_survey_getinlogs($link_id) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey_log WHERE link_id='$link_id' AND incoming='1' ORDER BY id";
	$db_result = dba_query($db_query);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[$i] = $db_row;
		$i++;
	}
	return $ret;
}

// get data by id
function sms_survey_getdatabyid($sid) {
	global $core_config;
	$ret = array();
	$cred = '';
	if ($core_config['user']['status'] && !($core_config['user']['status'] == 2)) {
		$cred = " AND uid='".$core_config['user']['uid']."'";
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey WHERE deleted='0' AND id='$sid'".$cred;
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row;
	}
	return $ret;
}

// get data by keyword
function sms_survey_getdatabykeyword($keyword) {
	global $core_config;
	$ret = array();
	$cred = '';
	if ($core_config['user']['status'] && !($core_config['user']['status'] == 2)) {
		$cred = " AND uid='".$core_config['user']['uid']."'";
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey WHERE deleted='0' AND keyword='$keyword'".$cred;
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row;
	}
	return $ret;
}

// get data all
function sms_survey_getdataall() {
	global $core_config;
	$ret = array();
	$cred = '';
	if ($core_config['user']['status'] && !($core_config['user']['status'] == 2)) {
		$cred = " AND uid='".$core_config['user']['uid']."'";
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey WHERE deleted='0'".$cred;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

// data add
function sms_survey_dataadd($keyword, $title) {
	global $core_config;
	// check available keyword in the system, it will returns TRUE if available
	if (checkavailablekeyword($keyword)) {
		$datetime_now = $core_config['datetime']['now'];
		$uid = $core_config['user']['uid'];
		$keyword = trim(strtoupper($keyword));
		$db_query = "INSERT INTO "._DB_PREF_."_featureSurvey (uid,keyword,title,creation_datetime) ";
		$db_query .= "VALUES ('$uid','$keyword','$title','$datetime_now')";
		$id = dba_insert_id($db_query);
	}
	return $id;
}

// data edit
function sms_survey_dataedit($sid, $keyword, $title) {
	global $core_config;
	$cred = '';
	if ($core_config['user']['status'] && !($core_config['user']['status'] == 2)) {
		$cred = " AND uid='".$core_config['user']['uid']."'";
	}
	$keyword = trim(strtoupper($keyword));
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',keyword='$keyword',title='$title' WHERE deleted='0' AND id='$sid'".$cred;
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// data del
function sms_survey_datadel($sid) {
	global $core_config;
	$cred = '';
	if ($core_config['user']['status'] && !($core_config['user']['status'] == 2)) {
		$cred = " AND uid='".$core_config['user']['uid']."'";
	}
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',deleted='1' WHERE deleted='0' AND id='$sid'".$cred;
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// enable
function sms_survey_dataenable($sid) {
	global $core_config;
	$cred = '';
	if ($core_config['user']['status'] && !($core_config['user']['status'] == 2)) {
		$cred = " AND uid='".$core_config['user']['uid']."'";
	}
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',status='1',started='0' WHERE deleted='0' AND id='$sid'".$cred;
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// disable
function sms_survey_datadisable($sid) {
	global $core_config;
	$cred = '';
	if ($core_config['user']['status'] && !($core_config['user']['status'] == 2)) {
		$cred = " AND uid='".$core_config['user']['uid']."'";
	}
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',status='0',started='0' WHERE deleted='0' AND id='$sid'".$cred;
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// start
function sms_survey_datastart($sid) {
	global $core_config;
	$cred = '';
	if ($core_config['user']['status'] && !($core_config['user']['status'] == 2)) {
		$cred = " AND uid='".$core_config['user']['uid']."'";
	}
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',status='1',started='1',running='0' WHERE deleted='0' AND id='$sid'".$cred;
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// stop
function sms_survey_datastop($sid) {
	global $core_config;
	$cred = '';
	if ($core_config['user']['status'] && !($core_config['user']['status'] == 2)) {
		$cred = " AND uid='".$core_config['user']['uid']."'";
	}
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',status='1',started='0',running='0' WHERE deleted='0' AND id='$sid'".$cred;
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// get members, all members in survey id
function sms_survey_getmembers($id) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey_members WHERE sid='$id' ORDER BY name";
	$db_result = dba_query($db_query);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[$i]['id'] = $db_row['id'];
		$ret[$i]['mobile'] = $db_row['mobile'];
		$ret[$i]['name'] = $db_row['name'];
		$i++;
	}
	return $ret;
}

// get member by id
function sms_survey_getmemberbyid($id) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey_members WHERE id='$id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret['sid'] = $db_row['sid'];
		$ret['mobile'] = $db_row['mobile'];
		$ret['name'] = $db_row['name'];
	}
	return $ret;
}

// get member by mobile
function sms_survey_getmemberbymobile($sid, $mobile) {
	$ret = array();
	$c_mobile = str_replace('+','',$mobile);
	if (strlen($c_mobile) > 7) { $c_mobile = substr($mobile,3); }
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey_members WHERE sid='$sid' AND mobile LIKE '%$c_mobile'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret['id'] = $db_row['id'];
		$ret['name'] = $db_row['name'];
	}
	return $ret;
}

// member add
function sms_survey_membersadd($sid, $mobile, $name) {
	$ret = false;
	$c_mobile = str_replace('+','',$mobile);
	if (strlen($c_mobile) > 7) { $c_mobile = substr($mobile,3); }
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey_members WHERE sid='$sid' AND mobile LIKE '%$c_mobile'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		if (($name == $db_row['name']) && ($mobile == $db_row['mobile'])) {
			$ret = true;
		} else {
			$db_query1 = "UPDATE "._DB_PREF_."_featureSurvey_members SET name='$name',mobile='$mobile' WHERE id='".$db_row['id']."'";
			$ret = dba_affected_rows($db_query1);
		}
	} else {
		$db_query = "INSERT INTO "._DB_PREF_."_featureSurvey_members (sid,mobile,name) VALUES ('$sid','$mobile','$name')";
		$ret = dba_insert_id($db_query);
	}
	return $ret;
}

// member del
function sms_survey_membersdel($sid, $id) {
	$ret = false;
	$db_query = "SELECT id FROM "._DB_PREF_."_featureSurvey_members WHERE sid='$sid' AND id='$id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$db_query1 = "DELETE FROM "._DB_PREF_."_featureSurvey_members WHERE sid='$sid' AND id='".$db_row['id']."'";
		$ret = dba_affected_rows($db_query1);
	}
	return $ret;
}

// get questions, all questions in survey id
function sms_survey_getquestions($id) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey_questions WHERE sid='$id' ORDER BY id";
	$db_result = dba_query($db_query);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[$i]['id'] = $db_row['id'];
		$ret[$i]['question'] = $db_row['question'];
		$i++;
	}
	return $ret;
}

// get question by id
function sms_survey_getquestionbyid($id) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey_questions WHERE id='$id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret['sid'] = $db_row['sid'];
		$ret['question'] = $db_row['question'];
	}
	return $ret;
}

// question add
function sms_survey_questionsadd($sid, $question) {
	$ret = false;
	$db_query = "INSERT INTO "._DB_PREF_."_featureSurvey_questions (sid,question) VALUES ('$sid','$question')";
	$ret = dba_insert_id($db_query);
	return $ret;
}

// question edit
function sms_survey_questionsedit($sid, $id, $question) {
	$ret = false;
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey_questions SET question='$question' WHERE sid='$sid' AND id='$id'";
	$ret = dba_affected_rows($db_query);
	return $ret;
}

// question del
function sms_survey_questionsdel($sid, $id) {
	$ret = false;
	$db_query = "SELECT id FROM "._DB_PREF_."_featureSurvey_questions WHERE sid='$sid' AND id='$id'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$db_query1 = "DELETE FROM "._DB_PREF_."_featureSurvey_questions WHERE sid='$sid' AND id='".$db_row['id']."'";
		$ret = dba_affected_rows($db_query1);
	}
	return $ret;
}

// save log
function sms_survey_savelog($arr) {
	$log_in_id = 0;
	if (is_array($arr)) {
		$fields = '';
		$values = '';
		foreach ($arr as $key => $val) {
			$fields .= $key.",";
			$values .= "'".$val."',";
		}
		$fields = substr($fields,0,-1);
		$values = substr($values,0,-1);
		$db_query = "INSERT INTO "._DB_PREF_."_featureSurvey_log (".$fields.") ";
		$db_query .= "VALUES (".$values.")";
		$log_in_id = dba_insert_id($db_query);
		//logger_print("savelog q:".$db_query, 3, "sms_survey");
	}
	return $log_in_id;
}

?>