<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php

/*
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword
 *   checkavailablekeyword() will insert keyword for checking to the hook here
 * @return 
 *   TRUE if keyword is available
 */
function inboxgroup_hook_checkavailablekeyword($keyword) {
	$ok = true;
	$db_query = "SELECT id FROM " . _DB_PREF_ . "_featureInboxgroup WHERE keywords LIKE '%$keyword%' AND deleted='0'";
	if ($db_result = dba_num_rows($db_query)) {
		$ok = false;
	}
	return $ok;
}

/*
 * intercept incoming sms and handle Inbox Group service
 *
 * @param $sms_datetime
 *   incoming SMS date/time
 * @param $sms_sender
 *   incoming SMS sender
 * @message
 *   incoming SMS message before interepted
 * @param $sms_receiver
 *   receiver number that is receiving incoming SMS
 * @return
 *   array $ret
 */
function inboxgroup_hook_interceptincomingsms($sms_datetime, $sms_sender, $message, $sms_receiver) {
	$ret = array();
	// proceed only when $message and $sms_receiver aren't empty
	if ($message && $sms_receiver) {
		// extract message to keyword and content, use keyword part only
		$msg = inboxgroup_extractmessage($message);
		if (($keyword = $msg['keyword']) && $msg['content']) {
			// get data from the combination of $sms_receiver and $keyword
			$data = inboxgroup_getdata($sms_receiver, $keyword);
			if ($data['id']) {
				// proceed only if receiver id exists and status is enabled
				if ($data['status']) {
					// save incoming SMS in log
					if ($log_in_id = inboxgroup_saveinlog($data['id'], $sms_datetime, $sms_sender, $keyword, $msg['content'], $sms_receiver)) {
						// forward to non catch all users (members, if any)
						inboxgroup_forwardmembers($data, $log_in_id, $sms_sender, $msg['content']);
						// set handled
						$ret['hooked'] = true;
					}
				}
			} else {
				// combination does not exists, check only $sms_receiver
				$data = inboxgroup_getdata($sms_receiver);
				// proceed only if receiver id exists
				if ($data['id'] && $data['status']) {
					// forward to catch all users (if any)
					inboxgroup_forwardcatchall($data, $log_in_id, $sms_sender, $msg['content']);
					// set handled
					$ret['hooked'] = true;
				}
			}
		}
	}
	return $ret;
}

function inboxgroup_forwardmembers($data, $log_in_id, $sms_sender, $message) {
	global $core_config;
	logger_print("forwardmembers id:".$data['id']." s:".$sms_sender." r:".$data['in_receiver']." m:".$message, 3, "inboxgroup");
	if ($username = uid2username($data['uid'])) {
		$users = inboxgroup_getmembers($data['id']);
		$continue = false;
		if ($data['exclusive']) {
			for ($i=0;$i<count($users);$i++) {
			if ($sms_sender == $users[$i]['mobile']) {
				$continue = true;
			}
			}
		} else {
			$continue = true;
		}
		if ($continue) {
			for ($i=0;$i<count($users);$i++) {
				if (($sms_to = $users[$i]['mobile']) && ($sms_to != $sms_sender)) {
					//list($ok, $to, $smslog_id,$queue) = sendsms($username, $sms_to, $message, 'text', 0);
					//logger_print("forwardmembers sendsms smslog_id:".$smslog_id[0]." to:".$sms_to, 2, "inboxgroup");
					//inboxgroup_saveoutlog($log_in_id, $smslog_id[0], 0, $users[$i]['uid']);
					$c_username = uid2username($users[$i]['uid']);
					insertsmstoinbox(core_get_datetime(),$sms_sender,$c_username,$message,$data['in_receiver']);
				}
			}
		}
	}
}

function inboxgroup_forwardcatchall($data, $log_in_id, $sms_sender, $message) {
	global $core_config;
	logger_print("forwardcatchall id:".$data['id']." s:".$sms_sender." r:".$data['in_receiver']." m:".$message, 3, "inboxgroup");
	if ($username = uid2username($data['uid'])) {
		$users = inboxgroup_getcatchall($data['id']);
		$continue = false;
		if ($data['exclusive']) {
			for ($i=0;$i<count($users);$i++) {
				if ($sms_sender == $users[$i]['mobile']) {
					$continue = true;
				}
			}
		} else {
			$continue = true;
		}
		if ($continue) {
			for ($i=0;$i<count($users);$i++) {
				if (($sms_to = $users[$i]['mobile']) && ($sms_to != $sms_sender)) {
					//list($ok, $to, $smslog_id,$queue) = sendsms($username, $sms_to, $message, 'text', 0);
					//logger_print("forwardcatchall sendsms smslog_id:".$smslog_id[0]." to:".$sms_to, 2, "inboxgroup");
					//inboxgroup_saveoutlog($log_in_id, $smslog_id[0], 1, $users[$i]['uid']);
					$c_username = uid2username($users[$i]['uid']);
					insertsmstoinbox(core_get_datetime(),$sms_sender,$c_username,$message,$data['in_receiver']);
				}
			}
		}
	}
}

function inboxgroup_extractmessage($message) {
	$ret = array();
	$arr = explode(" ", $message);
	$ret['keyword'] = trim(strtoupper($arr[0]));
	$ret['content'] = '';
	for ($i=1;$i<count($arr);$i++) {
		$ret['content'] .= $arr[$i]." ";
	}
	$ret['content'] = trim($ret['content']);
	//logger_print("extractmessage s:".$sms_sender." r:".$sms_receiver." k:".$ret['keyword']." c:".$ret['content'], 3, "inboxgroup");
	return $ret;
}

function inboxgroup_getdata($sms_receiver, $keyword) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureInboxgroup WHERE deleted='0' AND in_receiver='$sms_receiver' AND keywords LIKE '%".$keyword."%'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row;
	}
	return $ret;
}

function inboxgroup_getdatabyid($rid) {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureInboxgroup WHERE deleted='0' AND id='$rid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row;
	}
	return $ret;
}

function inboxgroup_getdataall() {
	$ret = array();
	$db_query = "SELECT * FROM "._DB_PREF_."_featureInboxgroup WHERE deleted='0'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function inboxgroup_getmembers($id) {
	$ret = array();
	$db_query = "SELECT uid FROM "._DB_PREF_."_featureInboxgroup_members WHERE rid='$id'";
	$db_result = dba_query($db_query);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$data = user_getdatabyuid($db_row['uid']);
		if ($data['uid']) {
			$ret[$i]['uid'] = $db_row['uid'];
			$ret[$i]['mobile'] = $data['mobile'];
			$i++;
		}
	}
	return $ret;
}

function inboxgroup_getcatchall($id) {
	$ret = array();
	$db_query = "SELECT uid FROM "._DB_PREF_."_featureInboxgroup_catchall WHERE rid='$id'";
	$db_result = dba_query($db_query);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$data = user_getdatabyuid($db_row['uid']);
		if ($data['mobile']) {
			$ret[$i]['uid'] = $db_row['uid'];
			$ret[$i]['mobile'] = $data['mobile'];
			$i++;
		}
	}
	return $ret;
}

function inboxgroup_saveinlog($rid, $sms_datetime, $sms_sender, $keyword, $message, $sms_receiver) {
	$db_query = "INSERT INTO "._DB_PREF_."_featureInboxgroup_log_in (rid,sms_datetime,sms_sender,keyword,message,sms_receiver) ";
	$db_query .= "VALUES ('$rid','$sms_datetime','$sms_sender','$keyword','$message','$sms_receiver')";
	$log_in_id = dba_insert_id($db_query);
	return $log_in_id;
}

function inboxgroup_saveoutlog($log_in_id, $smslog_id, $catchall, $uid) {
	$db_query = "INSERT INTO "._DB_PREF_."_featureInboxgroup_log_out (log_in_id,smslog_id,catchall,uid) ";
	$db_query .= "VALUES ('$log_in_id','$smslog_id','$catchall','$uid')";
	$log_out_id = dba_insert_id($db_query);
	return $log_out_id;
}

function inboxgroup_dataexists($in_receiver) {
	$ret = false;
	$db_query = "SELECT id FROM "._DB_PREF_."_featureInboxgroup WHERE deleted='0' AND in_receiver='$in_receiver'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = true;
	}
	return $ret;
}

function inboxgroup_dataadd($in_receiver, $keywords, $description) {
	global $core_config;
	$dt = core_get_datetime();
	$uid = $core_config['user']['uid'];
	$keywords = str_replace(' ', '', $keywords);
	$keywords = trim(strtoupper($keywords));
	$keywords = explode(',', $keywords);
	$k = '';
	for ($i=0;$i<count($keywords);$i++) {
		if (checkavailablekeyword($keywords[$i])) {
			$k .= $keywords[$i].',';
		}
	}
	if ($keywords = substr($k, 0, -1)) {
		$db_query = "INSERT INTO "._DB_PREF_."_featureInboxgroup (uid,in_receiver,keywords,description,creation_datetime) ";
		$db_query .= "VALUES ('$uid','$in_receiver','$keywords','$description','$dt')";
		$id = dba_insert_id($db_query);
	}
	return $id;
}

function inboxgroup_dataedit($rid, $keywords, $description, $exclusive) {
	$db_query = "SELECT keywords FROM "._DB_PREF_."_featureInboxgroup WHERE id='$rid'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$orig_keywords = explode(',', $db_row['keywords']);
	$exclusive = $exclusive ? 1 : 0 ; 
	$keywords = str_replace(' ', '', $keywords);
	$keywords = trim(strtoupper($keywords));
	$keywords = explode(',', $keywords);
	$k = '';
	for ($i=0;$i<count($keywords);$i++) {
		if (checkavailablekeyword($keywords[$i])) {
			$k .= $keywords[$i].',';
		} else {
			for ($j=0;$j<count($orig_keywords);$j++) {
				if ($keywords[$i] == $orig_keywords[$j]) {
					$k .= $keywords[$i].',';
				}
			}
		}
	}
	if ($keywords = substr($k, 0, -1)) {
		$db_query = "UPDATE "._DB_PREF_."_featureInboxgroup SET c_timestamp='".mktime()."',keywords='$keywords',description='$description',exclusive='$exclusive' WHERE deleted='0' AND id='$rid'";
		$db_result = dba_affected_rows($db_query);
	} else {
		$db_result = true;
	}
	return $db_result;
}

function inboxgroup_datadel($rid) {
	$db_query = "UPDATE "._DB_PREF_."_featureInboxgroup SET c_timestamp='".mktime()."',deleted='1' WHERE deleted='0' AND id='$rid'";
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

function inboxgroup_dataenable($rid) {
	$db_query = "UPDATE "._DB_PREF_."_featureInboxgroup SET c_timestamp='".mktime()."',status='1' WHERE deleted='0' AND id='$rid'";
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

function inboxgroup_datadisable($rid) {
	$db_query = "UPDATE "._DB_PREF_."_featureInboxgroup SET c_timestamp='".mktime()."',status='0' WHERE deleted='0' AND id='$rid'";
	$db_result = dba_affected_rows($db_query);
	return $db_result;
}

function inboxgroup_membersadd($rid, $uid) {
	$ret = false;
	$db_query = "SELECT id FROM "._DB_PREF_."_featureInboxgroup_members WHERE rid='$rid' AND uid='$uid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = true;
	} else {
		$db_query = "INSERT INTO "._DB_PREF_."_featureInboxgroup_members (rid,uid) VALUES ('$rid','$uid')";
		$ret = dba_insert_id($db_query);
	}
	return $ret;
}

function inboxgroup_membersdel($rid, $uid) {
	$ret = false;
	$db_query = "SELECT id FROM "._DB_PREF_."_featureInboxgroup_members WHERE rid='$rid' AND uid='$uid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$db_query = "DELETE FROM "._DB_PREF_."_featureInboxgroup_members WHERE rid='$rid' AND uid='$uid'";
		$ret = dba_affected_rows($db_query);
	}
	return $ret;
}

function inboxgroup_catchalladd($rid, $uid) {
	$ret = false;
	$db_query = "SELECT id FROM "._DB_PREF_."_featureInboxgroup_catchall WHERE rid='$rid' AND uid='$uid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = true;
	} else {
		$db_query = "INSERT INTO "._DB_PREF_."_featureInboxgroup_catchall (rid,uid) VALUES ('$rid','$uid')";
		$ret = dba_insert_id($db_query);
	}
	return $ret;
}

function inboxgroup_catchalldel($rid, $uid) {
	$ret = false;
	$db_query = "SELECT id FROM "._DB_PREF_."_featureInboxgroup_catchall WHERE rid='$rid' AND uid='$uid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$db_query = "DELETE FROM "._DB_PREF_."_featureInboxgroup_catchall WHERE rid='$rid' AND uid='$uid'";
		$ret = dba_affected_rows($db_query);
	}
	return $ret;
}

?>