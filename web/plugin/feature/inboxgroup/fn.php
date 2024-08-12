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
 * Check keyword availability.
 * This function hooks keyword_isavail()
 *
 * @param string $keyword keyword to be checked
 * @return bool true if keyword is available
 */
function inboxgroup_hook_keyword_isavail($keyword)
{
	$ret = true;

	$db_query = "SELECT id FROM " . _DB_PREF_ . "_featureInboxgroup WHERE keywords=? AND deleted='0'";
	if (dba_num_rows($db_query, [$keyword])) {
		$ret = false;
	}

	return $ret;
}

/**
 * Intercept incoming sms and handle Inbox Group service
 *
 * @param string $sms_datetime incoming SMS date/time
 * @param string $sms_sender incoming SMS sender
 * @param string $message incoming SMS message before intercepted
 * @param string $sms_receiver receiver number that is receiving incoming SMS
 * @return array
 *     [
 *         'hooked',	// true if SMS intercepted
 *     ]
 */
function inboxgroup_hook_recvsms_process_before($sms_datetime, $sms_sender, $message, $sms_receiver)
{
	$ret = [];

	// proceed only when $message and $sms_receiver aren't empty
	if ($message && $sms_receiver) {
		// extract message to keyword and content, use keyword part only
		$msg = inboxgroup_extractmessage($message);
		if (($keyword = $msg['keyword']) && $msg['content'] && $msg['full']) {
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
					// save incoming SMS in log
					if ($log_in_id = inboxgroup_saveinlog($data['id'], $sms_datetime, $sms_sender, $keyword, $msg['full'], $sms_receiver)) {
						// forward to non catch all users (members, if any)
						inboxgroup_forwardcatchall($data, $log_in_id, $sms_sender, $msg['full']);
						// set handled
						$ret['hooked'] = true;
					}
				}
			}
		}
	}

	return $ret;
}

function inboxgroup_forwardmembers($data, $log_in_id, $sms_sender, $message)
{
	global $core_config;

	_log("forwardmembers id:" . $data['id'] . " s:" . $sms_sender . " r:" . $data['in_receiver'] . " m:" . $message, 3, "inboxgroup");
	if (user_uid2username($data['uid'])) {
		$users = inboxgroup_getmembers($data['id']);
		$c_count = is_array($users) ? count($users) : 0;
		$continue = false;
		if ($data['exclusive']) {
			for ($i = 0; $i < $c_count; $i++) {
				if ($sms_sender && $sms_sender == $users[$i]['mobile']) {
					$continue = true;
					break;
				}
			}
		} else {
			$continue = true;
		}
		if ($continue) {
			for ($i = 0; $i < $c_count; $i++) {
				if (($sms_to = $users[$i]['mobile']) && ($sms_to != $sms_sender)) {
					//list($ok, $to, $smslog_id,$queue) = sendsms_helper($username, $sms_to, $message, 'text', 0);
					//_log("forwardmembers sendsms smslog_id:".$smslog_id[0]." to:".$sms_to, 2, "inboxgroup");
					//inboxgroup_saveoutlog($log_in_id, $smslog_id[0], 0, $users[$i]['uid']);
					$c_username = user_uid2username($users[$i]['uid']);
					recvsms_inbox_add(core_get_datetime(), $sms_sender, $c_username, $message, $data['in_receiver']);
				}
			}
		}
	}
}

function inboxgroup_forwardcatchall($data, $log_in_id, $sms_sender, $message)
{
	global $core_config;

	_log("forwardcatchall id:" . $data['id'] . " s:" . $sms_sender . " r:" . $data['in_receiver'] . " m:" . $message, 3, "inboxgroup");
	if (user_uid2username($data['uid'])) {
		$users = inboxgroup_getcatchall($data['id']);
		$c_count = is_array($users) ? count($users) : 0;
		$continue = false;
		if ($data['exclusive']) {
			for ($i = 0; $i < $c_count; $i++) {
				if ($sms_sender && $sms_sender == $users[$i]['mobile']) {
					$continue = true;
					break;
				}
			}
		} else {
			$continue = true;
		}
		if ($continue) {
			for ($i = 0; $i < $c_count; $i++) {
				if (($sms_to = $users[$i]['mobile']) && ($sms_to != $sms_sender)) {
					//list($ok, $to, $smslog_id,$queue) = sendsms_helper($username, $sms_to, $message, 'text', 0);
					//_log("forwardcatchall sendsms smslog_id:".$smslog_id[0]." to:".$sms_to, 2, "inboxgroup");
					//inboxgroup_saveoutlog($log_in_id, $smslog_id[0], 1, $users[$i]['uid']);
					$c_username = user_uid2username($users[$i]['uid']);
					recvsms_inbox_add(core_get_datetime(), $sms_sender, $c_username, $message, $data['in_receiver']);
				}
			}
		}
	}
}

function inboxgroup_extractmessage($message)
{
	$ret = [];

	$arr = explode(' ', $message, 2);
	$ret['keyword'] = trim(strtoupper($arr[0]));
	$ret['content'] = trim($arr[1]);
	$ret['full'] = trim($message);
	$ret['raw'] = $message;

	return $ret;
}

function inboxgroup_getdata($sms_receiver, $keyword = '')
{
	$ret = [];

	$db_argv[] = $sms_receiver;
	if ($keyword = core_sanitize_keyword($keyword)) {
		$the_keyword = "AND keywords=?";
		$db_argv[] = trim($keyword);
	}
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureInboxgroup WHERE deleted='0' AND in_receiver=? " . $the_keyword;
	$db_result = dba_query($db_query, $db_argv);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row;
	}

	return $ret;
}

function inboxgroup_getdatabyid($id)
{
	$ret = [];

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureInboxgroup WHERE deleted='0' AND id=?";
	$db_result = dba_query($db_query, [$id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row;
	}

	return $ret;
}

function inboxgroup_getdataall()
{
	$ret = [];

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureInboxgroup WHERE deleted='0'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}

	return $ret;
}

function inboxgroup_getmembers($rid)
{
	$ret = [];

	$db_query = "SELECT uid FROM " . _DB_PREF_ . "_featureInboxgroup_members WHERE rid=?";
	$db_result = dba_query($db_query, [$rid]);
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

function inboxgroup_getcatchall($rid)
{
	$ret = [];

	$db_query = "SELECT uid FROM " . _DB_PREF_ . "_featureInboxgroup_catchall WHERE rid=?";
	$db_result = dba_query($db_query, [$rid]);
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

function inboxgroup_saveinlog($rid, $sms_datetime, $sms_sender, $keyword, $message, $sms_receiver)
{
	$db_query = "INSERT INTO " . _DB_PREF_ . "_featureInboxgroup_log_in (rid,sms_datetime,sms_sender,keyword,message,sms_receiver) VALUES (?,?,?,?,?,?)";
	$ret = dba_insert_id($db_query, [$rid, $sms_datetime, $sms_sender, $keyword, $message, $sms_receiver]);

	return $ret;
}

function inboxgroup_saveoutlog($log_in_id, $smslog_id, $catchall, $uid)
{
	$db_query = "INSERT INTO " . _DB_PREF_ . "_featureInboxgroup_log_out (log_in_id,smslog_id,catchall,uid) VALUES (?,?,?,?)";
	$ret = dba_insert_id($db_query, [$log_in_id, $smslog_id, $catchall, $uid]);

	return $ret;
}

function inboxgroup_dataexists($in_receiver)
{
	$ret = false;

	$db_query = "SELECT id FROM " . _DB_PREF_ . "_featureInboxgroup WHERE deleted='0' AND in_receiver=?";
	if (dba_num_rows($db_query, [$in_receiver])) {
		$ret = true;
	}

	return $ret;
}

function inboxgroup_dataadd($in_receiver, $keywords, $description)
{
	global $user_config;

	$id = null;

	$dt = core_get_datetime();
	$uid = $user_config['uid'];
	$keywords = trim(strtoupper(preg_replace('/[\s]+/', '', $keywords)));
	$keywords = explode(',', $keywords);
	$k = '';
	foreach ( $keywords as $keyword ) {
		$keyword = core_sanitize_keyword($keyword);
		if (keyword_isavail($keyword)) {
			$k .= $keyword . ',';
		}
	}
	if ($k && $keywords = substr($k, 0, -1)) {
		$db_query = "INSERT INTO " . _DB_PREF_ . "_featureInboxgroup (uid,in_receiver,keywords,description,creation_datetime) VALUES (?,?,?,?,?)";
		$id = dba_insert_id($db_query, [$uid, $in_receiver, $keywords, $description, $dt]);
	}

	return $id;
}

function inboxgroup_dataedit($id, $keywords, $description, $exclusive)
{
	$db_query = "SELECT keywords FROM " . _DB_PREF_ . "_featureInboxgroup WHERE id=?";
	$db_result = dba_query($db_query, [$id]);
	$db_row = dba_fetch_array($db_result);
	$orig_keywords = explode(',', $db_row['keywords']);
	$exclusive = $exclusive ? 1 : 0;
	$keywords = preg_replace('/[\s]+/', '', $keywords);
	$keywords = trim(strtoupper($keywords));
	$keywords = explode(',', $keywords);
	$k = '';
	foreach ( $keywords as $keyword ) {
		$keyword = core_sanitize_keyword($keyword);
		if (keyword_isavail($keyword)) {
			$k .= $keyword . ',';
		} else {
			foreach ( $orig_keywords as $orig_keyword ) {
				if ($keyword && $keyword == $orig_keyword) {
					$k .= $keyword . ',';
				}
			}
		}
	}
	if ($k && $keywords = substr($k, 0, -1)) {
		$db_query = "UPDATE " . _DB_PREF_ . "_featureInboxgroup SET c_timestamp='" . time() . "',keywords=?,description=?,exclusive=? WHERE deleted='0' AND id=?";
		$ret = dba_affected_rows($db_query, [$keywords, $description, $exclusive, $id]);
	} else {
		$ret = true;
	}

	return $ret;
}

function inboxgroup_datadel($id)
{
	$db_query = "UPDATE " . _DB_PREF_ . "_featureInboxgroup SET c_timestamp='" . time() . "',deleted='1' WHERE deleted='0' AND id=?";
	$ret = dba_affected_rows($db_query, [$id]);

	return $ret;
}

function inboxgroup_dataenable($id)
{
	$db_query = "UPDATE " . _DB_PREF_ . "_featureInboxgroup SET c_timestamp='" . time() . "',status='1' WHERE deleted='0' AND id=?";
	$db_result = dba_affected_rows($db_query, [$id]);
	return $db_result;
}

function inboxgroup_datadisable($id)
{
	$db_query = "UPDATE " . _DB_PREF_ . "_featureInboxgroup SET c_timestamp='" . time() . "',status='0' WHERE deleted='0' AND id=?";
	$db_result = dba_affected_rows($db_query, [$id]);
	return $db_result;
}

function inboxgroup_membersadd($rid, $uid)
{
	$ret = false;

	$db_query = "SELECT id FROM " . _DB_PREF_ . "_featureInboxgroup_members WHERE rid=? AND uid=?";
	if (dba_num_rows($db_query, [$rid, $uid])) {
		$ret = true;
	} else {
		$db_query = "INSERT INTO " . _DB_PREF_ . "_featureInboxgroup_members (rid,uid) VALUES (?,?)";
		$ret = dba_insert_id($db_query, [$rid, $uid]);
	}

	return $ret;
}

function inboxgroup_membersdel($rid, $uid)
{
	$ret = false;

	$db_query = "SELECT id FROM " . _DB_PREF_ . "_featureInboxgroup_members WHERE rid=? AND uid=?";
	if (dba_num_rows($db_query, [$rid, $uid])) {
		$db_query = "DELETE FROM " . _DB_PREF_ . "_featureInboxgroup_members WHERE rid=? AND uid=?";
		$ret = dba_affected_rows($db_query, [$rid, $uid]);
	}

	return $ret;
}

function inboxgroup_catchalladd($rid, $uid)
{
	$ret = false;

	$db_query = "SELECT id FROM " . _DB_PREF_ . "_featureInboxgroup_catchall WHERE rid=? AND uid=?";
	if (dba_num_rows($db_query, [$rid, $uid])) {
		$ret = true;
	} else {
		$db_query = "INSERT INTO " . _DB_PREF_ . "_featureInboxgroup_catchall (rid,uid) VALUES (?,?)";
		$ret = dba_insert_id($db_query, [$rid, $uid]);
	}

	return $ret;
}

function inboxgroup_catchalldel($rid, $uid)
{
	$ret = false;

	$db_query = "SELECT id FROM " . _DB_PREF_ . "_featureInboxgroup_catchall WHERE rid=? AND uid=?";
	if (dba_num_rows($db_query, [$rid, $uid])) {
		$db_query = "DELETE FROM " . _DB_PREF_ . "_featureInboxgroup_catchall WHERE rid=? AND uid=?";
		$ret = dba_affected_rows($db_query, [$rid, $uid]);
	}

	return $ret;
}
