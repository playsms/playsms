<?php


/*
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword
 *   checkavailablekeyword() will insert keyword for checking to the hook here
 * @return 
 *   TRUE if keyword is NOT available
 */
function sms_survey_hook_checkavailablekeyword($keyword) {
	$ok = false;
	$db_query = "SELECT id FROM " . _DB_PREF_ . "_featureSurvey WHERE keyword='$keyword'";
	if ($db_result = dba_num_rows($db_query)) {
		$ok = true;
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
function sms_survey_hook_setsmsincomingaction($sms_datetime, $sms_sender, $survey_keyword, $survey_param = '', $sms_receiver = '') {
	$ok = false;
	$db_query = "SELECT uid FROM " . _DB_PREF_ . "_featureSurvey WHERE keyword='$survey_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$c_uid = $db_row['uid'];
		if (sms_survey_handle($c_uid, $sms_datetime, $sms_sender, $survey_keyword, $survey_param)) {
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

// handle survey
function sms_survey_handle($c_uid, $sms_datetime, $sms_sender, $survey_keyword, $survey_param = '') {
	$ok = false;
	$username = uid2username($c_uid);
	$sms_to = $sms_sender; // we are replying to this sender
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSurvey WHERE keyword='$survey_keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	if ($db_row['status'] == 1) {
		// later
	} else if ($db_row['survey_keyword'] == $survey_keyword) {
	    	// returns true even if its logged as correct/incorrect answer
	    	// this situation happens when user answers a disabled survey
	    	// returning false will make this SMS as unhandled SMS
	    	$ok = true;
	}
	return $ok;
}

// get data by id
function sms_survey_getdatabyid($sid) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey WHERE deleted='0' AND id='$sid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row;
	}
	return $ret;
}

// get data all
function sms_survey_getdataall() {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey WHERE deleted='0'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

// data add
function sms_survey_dataadd($keyword, $title) {
	global $core_config;
	$datetime_now = $core_config['datetime']['now'];
	$uid = $core_config['user']['uid'];
	$keyword = trim(strtoupper($keyword));
	$db_query = "INSERT INTO "._DB_PREF_."_featureSurvey (uid,keyword,title,creation_datetime) ";
	$db_query .= "VALUES ('$uid','$keyword','$title','$datetime_now')";
	$id = dba_insert_id($db_query);
	return $id;
}

// data edit
function sms_survey_dataedit($sid, $keyword, $title) {
	$keyword = trim(strtoupper($keyword));
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',keyword='$keyword',title='$title' WHERE deleted='0' AND id='$sid'";
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// data del
function sms_survey_datadel($sid) {
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',deleted='1' WHERE deleted='0' AND id='$sid'";
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// enable
function sms_survey_dataenable($sid) {
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',status='1' WHERE deleted='0' AND id='$sid'";
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// disable
function sms_survey_datadisable($sid) {
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',status='0' WHERE deleted='0' AND id='$sid'";
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// start
function sms_survey_datastart($sid) {
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',status='1',started='1' WHERE deleted='0' AND id='$sid'";
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// stop
function sms_survey_datastop($sid) {
	$db_query = "UPDATE "._DB_PREF_."_featureSurvey SET c_timestamp='".mktime()."',status='1',started='0' WHERE deleted='0' AND id='$sid'";
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

// get members
function sms_survey_getmembers($id) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureSurvey_members WHERE sid='$id'";
	$db_result = dba_query($db_query);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[$i]['id'] = $db_row['id'];
		$ret[$i]['name'] = $db_row['name'];
		$ret[$i]['mobile'] = $db_row['mobile'];
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
		$ret['name'] = $db_row['name'];
		$ret['mobile'] = $db_row['mobile'];
	}
	return $ret;
}

// member add
function sms_survey_membersadd($sid, $mobile, $name) {
	$ret = false;
	$c_mobile = str_replace('+','',$mobile);
	if (strlen($c_mobile) > 7) { $c_mobile = substr($mobile,3); }
	$db_query = "SELECT id FROM "._DB_PREF_."_featureSurvey_members WHERE sid='$sid' AND mobile LIKE '%$c_mobile'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$db_query1 = "UPDATE "._DB_PREF_."_featureSurvey_members SET name='$name',mobile='$mobile' WHERE id='".$db_row['id']."'";
		$ret = dba_affected_rows($db_query1);
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

// get questions
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

function sms_survey_saveinlog($sid, $sms_datetime, $sms_sender, $keyword, $message, $sms_receiver) {
	$db_query = "INSERT INTO "._DB_PREF_."_featureSurvey_log_in (sid,sms_datetime,sms_sender,keyword,message,sms_receiver) ";
	$db_query .= "VALUES ('$sid','$sms_datetime','$sms_sender','$keyword','$message','$sms_receiver')";
	$log_in_id = dba_insert_id($db_query);
	return $log_in_id;
}

function sms_survey_saveoutlog($log_in_id, $smslog_id, $questions, $uid) {
	$db_query = "INSERT INTO "._DB_PREF_."_featureSurvey_log_out (log_in_id,smslog_id,questions,uid) ";
	$db_query .= "VALUES ('$log_in_id','$smslog_id','$questions','$uid')";
	$log_out_id = dba_insert_id($db_query);
	return $log_out_id;
}

?>