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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

function phonebook_hook_phonebook_groupid2name($gpid) {
	if ($gpid) {
		$db_query = "SELECT name FROM "._DB_PREF_."_toolsPhonebook_group WHERE id='$gpid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$name = $db_row['name'];
	}
	return $name;
}

function phonebook_hook_phonebook_groupname2id($uid,$name) {
	if ($uid && $name) {
		$db_query = "SELECT id FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid' AND code='$code'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$id = $db_row['id'];
	}
	return $id;
}

function phonebook_hook_phonebook_groupid2code($gpid) {
	if ($gpid) {
		$db_query = "SELECT code FROM "._DB_PREF_."_toolsPhonebook_group WHERE id='$gpid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$code = $db_row['code'];
	}
	return $code;
}

function phonebook_hook_phonebook_groupcode2id($uid,$code) {
	if ($uid && $code) {
		$db_query = "SELECT id FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid' AND code='$code'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$id = $db_row['id'];
	}
	return $id;
}

function phonebook_hook_phonebook_number2name($mobile, $c_username='') {
	global $core_config;
	$name = '';
	if ($mobile) {
		// if username supplied use it, else use global username
		$c_uid = user_username2uid($c_username);
		$uid = $c_uid ? $c_uid : $core_config['user']['uid'];
		// remove +
		$mobile = str_replace('+','',$mobile);
		// remove first 3 digits if phone number length more than 7
		if (strlen($mobile) > 7) { $mobile = substr($mobile,3); }
		$db_query = "
			SELECT A.name AS name FROM "._DB_PREF_."_toolsPhonebook AS A
			INNER JOIN "._DB_PREF_."_toolsPhonebook_group AS B ON A.uid=B.uid
			INNER JOIN "._DB_PREF_."_toolsPhonebook_group_contacts AS C ON A.id=C.pid AND B.id=C.gpid
			WHERE A.mobile LIKE '%".$mobile."' AND B.uid='$uid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$name = $db_row['name'];
	}
	return $name;
}

function phonebook_hook_phonebook_getmembercountbyid($gpid) {
	$count = 0;
	$db_query = "SELECT COUNT(*) as count FROM "._DB_PREF_."_toolsPhonebook_group_contacts WHERE gpid='$gpid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$count = ( $db_row['count'] ? $db_row['count'] : 0 );
	}
	return $count;
}

function phonebook_hook_phonebook_getdatabyid($gpid, $orderby="") {
	$ret = array();
	$db_query = "
		SELECT A.id AS pid, A.name AS p_desc, A.mobile AS p_num, A.email AS email
		FROM "._DB_PREF_."_toolsPhonebook AS A
		INNER JOIN "._DB_PREF_."_toolsPhonebook_group AS B ON A.uid=B.uid
		INNER JOIN "._DB_PREF_."_toolsPhonebook_group_contacts AS C ON A.id=C.pid AND B.id=C.gpid
		WHERE B.id='$gpid'";
	if ($orderby) {
		$db_query .= " ORDER BY ".$orderby;
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_getdatabyuid($uid, $orderby="") {
	$ret = array();
	$db_query = "
		SELECT A.id AS pid, A.name AS p_desc, A.mobile AS p_num, A.email AS email
		FROM "._DB_PREF_."_toolsPhonebook AS A
		INNER JOIN "._DB_PREF_."_toolsPhonebook_group AS B ON A.uid=B.uid
		INNER JOIN "._DB_PREF_."_toolsPhonebook_group_contacts AS C ON A.id=C.pid AND B.id=C.gpid
		WHERE B.uid='$uid'";
	if ($orderby) {
		$db_query .= " ORDER BY ".$orderby;
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_getgroupbyid($gpid, $orderby="") {
	$ret = array();
	$db_query = "SELECT id AS gpid, name AS gp_name, code AS gp_code, flag_sender FROM "._DB_PREF_."_toolsPhonebook_group WHERE id='$gpid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_getgroupbyuid($uid, $orderby="") {
	$ret = array();
	$db_query = "SELECT id AS gpid, name AS gp_name, code AS gp_code, flag_sender FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid'";
	if ($orderby) {
		$db_query .= " ORDER BY ".$orderby;
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_search($uid, $keyword="", $count=0) {
	$ret = array();
	if ($keyword) {
		$fields = 'DISTINCT A.id AS pid, A.name AS p_desc, A.mobile AS p_num, A.email AS email';
		$join = "INNER JOIN "._DB_PREF_."_toolsPhonebook_group AS B ON A.uid=B.uid ";
		$join .= "INNER JOIN "._DB_PREF_."_toolsPhonebook_group_contacts AS C ON A.id=C.pid AND B.id=C.gpid";
		$conditions = array('A.uid' => $uid);
		$keywords = array('A.name' => '%'.$keyword.'%', 'A.mobile' => '%'.$keyword.'%', 'A.email' => '%'.$keyword.'%');
		if ($count > 0) {
			$extras = array('LIMIT' => $count);
		}
		$ret = dba_search(_DB_PREF_.'_toolsPhonebook AS A', $fields, $conditions, $keywords, $extras, $join);
	}
	return $ret;
}

function phonebook_hook_phonebook_search_group($uid, $keyword="", $count=0) {
	$ret = array();
	$fields = 'id AS gpid, name AS group_name, code, flag_sender';
	$conditions = array('uid' => $uid);
	if ($keyword) {
		$keywords = array('name' => '%'.$keyword.'%', 'code' => '%'.$keyword.'%');
	}
	if ($count > 0) {
		$extras = array('LIMIT' => $count);
	}
	$ret = dba_search(_DB_PREF_.'_toolsPhonebook_group', $fields, $conditions, $keywords, $extras);
	return $ret;
}

function phonebook_hook_phonebook_search_user($uid=0, $keyword="", $count=0) {
	$ret = array();
	if ($uid > 0) {
		$conditions = array('uid' => $uid);
	}
	if ($keyword) {
		$keywords = array('name' => '%'.$keyword.'%', 'username' => '%'.$keyword.'%');
	}
	if ($count > 0) {
		$extras = array('LIMIT' => $count);
	}
	$ret = dba_search(_DB_PREF_.'_tblUser', '*', $conditions, $keywords, $extras);
	return $ret;
}

function phonebook_hook_webservices_output($ta,$requests) {
	global $core_config;
	if (! auth_isvalid()) {
		return FALSE;
	}
	$keyword = $requests['keyword'];
	if (!$keyword) {
		$keyword = $requests['tag'];
	}
	if ($keyword && $core_config['user']['uid']) {
		if (substr($keyword, 0, 1) == '@') {
			$keyword = substr($keyword, 1);
			$list = phonebook_search_user(0, $keyword);
			foreach ($list as $data) {
				$item[] = array('id' => '@'.$data['username'], 'text' => '@'.$data['name']);
			}
		} else if (substr($keyword, 0, 1) == '#') {
			$keyword = substr($keyword, 1);
			$list = phonebook_search_group($core_config['user']['uid'], $keyword);
			foreach ($list as $data) {
				$item[] = array('id' => '#'.$data['code'], 'text' => _('Group').': '.$data['group_name'].' ('.$data['code'].')');
			}
		} else {
			$list = phonebook_search($core_config['user']['uid'], $keyword);
			foreach ($list as $data) {
				$item[] = array('id' => $data['p_num'], 'text' => $data['p_desc'].' ('.$data['p_num'].')');
			}
		}
		if (count($item) == 0) {
			$item[] = array('id' => $keyword, 'text' => $keyword);
		}
		$content = json_encode($item);
		ob_end_clean();
		header('Content-Type: text/json; charset=utf-8');
		$ret = $content;
	}
	return $ret;
}

function phonebook_hook_recvsms_intercept($sms_datetime, $sms_sender, $message, $sms_receiver) {
	$ret = array();
	// continue only when keyword does not exists
	$m = explode(' ', $message);
	if (! checkavailablekeyword($m[0])) {
		return $ret;
	}
	logger_print("recvsms_intercept dt:".core_display_datetime($sms_datetime)." s:".$sms_sender." r:".$sms_receiver." m:".$message, 3, "phonebook");
	// scan for #<sender's phonebook group code> and @<username>
	$found_bc = FALSE;
	$found_pv = FALSE;
	$msg = explode(' ', $message);
	if (count($msg) > 1) {
		$bc = array();
		$pv = array();
		for ($i=0;$i<count($msg);$i++) {
			$c_text = trim($msg[$i]);
			if (substr($c_text, 0, 1) === '#') {
				$bc[] = strtoupper(substr($c_text, 1));
				$found_bc = TRUE;
			}
			if (substr($c_text, 0, 1) === '@') {
				$pv[] = strtolower(substr($c_text, 1));
				$found_pv = TRUE;
			}
		}
	}
	if ($found_bc) {
		$groups = array_unique($bc);
		foreach ($groups as $key => $c_group_code) {
			$c_group_code = strtoupper($c_group_code);
			$c_group_code = core_sanitize_alphanumeric($c_group_code);
			$c_uid = user_mobile2uid($sms_sender);
			if ($c_uid && ($c_gpid = phonebook_groupcode2id($c_uid, $c_group_code))) {
				$c_username = user_uid2username($c_uid);
				logger_print("bc g:".$c_group_code." gpid:".$c_gpid." uid:".$c_uid." dt:".core_display_datetime($sms_datetime)." s:".$sms_sender." r:".$sms_receiver." m:".$message, 3, "phonebook");
				sendsms_bc($c_username, $c_gpid, $message);
				logger_print("bc end", 3, "phonebook");
				$ret['uid'] = $c_uid;
				$ret['hooked'] = true;
			}
		}
	}
	if ($found_pv) {
		$users = array_unique($pv);
		foreach ($users as $key => $c_username) {
			$c_username = core_sanitize_username($c_username);
			if ($c_uid = user_username2uid($c_username)) {
				logger_print("pv u:".$c_username." uid:".$c_uid." dt:".core_display_datetime($sms_datetime)." s:".$sms_sender." r:".$sms_receiver." m:".$message, 3, "phonebook");
				recvsms_inbox_add(core_display_datetime($sms_datetime), $sms_sender, $c_username, $message, $sms_receiver);
				logger_print("pv end", 3, "phonebook");
				$ret['uid'] = $c_uid;
				$ret['hooked'] = true;
			}
		}
	}
	return $ret;
}
