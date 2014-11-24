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

function phonebook_hook_phonebook_groupid2name($gpid) {
	if ($gpid) {
		$db_query = "SELECT name FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE id='$gpid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$name = $db_row['name'];
	}
	return $name;
}

function phonebook_hook_phonebook_groupname2id($uid, $name) {
	if ($uid && $name) {
		$db_query = "SELECT id FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE uid='$uid' AND code='$code'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$id = $db_row['id'];
	}
	return $id;
}

function phonebook_hook_phonebook_groupid2code($gpid) {
	if ($gpid) {
		$db_query = "SELECT code FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE id='$gpid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$code = $db_row['code'];
	}
	return $code;
}

function phonebook_hook_phonebook_groupcode2id($uid, $code) {
	if ($uid && $code) {
		$db_query = "SELECT id FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE uid='$uid' AND code='$code'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$id = $db_row['id'];
	}
	return $id;
}

function phonebook_hook_phonebook_number2name($mobile, $c_username = '') {
	global $user_config;
	$name = '';
	if ($mobile) {
		
		// if username supplied use it, else use global username
		$c_uid = user_username2uid($c_username);
		$uid = $c_uid ? $c_uid : $user_config['uid'];
		
		// remove +
		$mobile = str_replace('+', '', $mobile);
		
		// remove first 3 digits if phone number length more than 7
		if (strlen($mobile) > 7) {
			$mobile = substr($mobile, 3);
		}
		$db_query = "
			SELECT A.name AS name FROM " . _DB_PREF_ . "_featurePhonebook AS A
			LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid
			LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid
			WHERE A.mobile LIKE '%" . $mobile . "' AND (
				A.uid='$uid'
				OR B.id in
					(
					SELECT B.id AS id FROM " . _DB_PREF_ . "_featurePhonebook AS A
					LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid
					LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid
					WHERE A.mobile='" . user_getfieldbyuid($uid, 'mobile') . "' AND B.flag_sender='1' 
					)
				OR ( A.uid<>'$uid' AND B.flag_sender>'1' ) )
			LIMIT 1";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$name = $db_row['name'];
	}
	return $name;
}

function phonebook_hook_phonebook_getmembercountbyid($gpid) {
	$count = 0;
	$db_query = "SELECT COUNT(*) as count FROM " . _DB_PREF_ . "_featurePhonebook_group_contacts WHERE gpid='$gpid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$count = ($db_row['count'] ? $db_row['count'] : 0);
	}
	return $count;
}

function phonebook_hook_phonebook_getdatabyid($gpid, $orderby = "") {
	$ret = array();
	$db_query = "
		SELECT A.id AS pid, A.name AS p_desc, A.mobile AS p_num, A.email AS email
		FROM " . _DB_PREF_ . "_featurePhonebook AS A
		INNER JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON A.uid=B.uid
		INNER JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid AND B.id=C.gpid
		WHERE B.id='$gpid'";
	if ($orderby) {
		$db_query .= " ORDER BY " . $orderby;
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_getdatabyuid($uid, $orderby = "") {
	$ret = array();
	$db_query = "
		SELECT DISTINCT A.id AS pid, A.name AS p_desc, A.mobile AS p_num, A.email AS email
		FROM " . _DB_PREF_ . "_featurePhonebook AS A
		LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON A.uid=B.uid
		LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid AND B.id=C.gpid
		WHERE A.uid='$uid'";
	if ($orderby) {
		$db_query .= " ORDER BY " . $orderby;
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_getgroupbyid($gpid, $orderby = "") {
	$ret = array();
	$db_query = "SELECT id AS gpid, name AS gp_name, code AS gp_code, flag_sender FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE id='$gpid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$ret = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_getgroupbyuid($uid, $orderby = "") {
	$ret = array();
	$db_query = "SELECT id AS gpid, name AS gp_name, code AS gp_code, flag_sender FROM " . _DB_PREF_ . "_featurePhonebook_group WHERE uid='$uid'";
	if ($orderby) {
		$db_query .= " ORDER BY " . $orderby;
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_search($uid, $keyword = "", $count = 0) {
	$ret = array();
	if ($keyword) {
		$db_query = "
			SELECT DISTINCT A.id AS pid, A.name AS p_desc, A.mobile AS p_num, A.email AS email
			FROM " . _DB_PREF_ . "_featurePhonebook AS A
                        LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid
                        LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid
			WHERE (
				A.uid='$uid' OR
				B.id in (
					SELECT B.id AS id FROM " . _DB_PREF_ . "_featurePhonebook AS A
					LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid
					LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid
					WHERE A.mobile='" . user_getfieldbyuid($uid, 'mobile') . "' AND B.flag_sender='1'
				) OR (
				A.uid <>'$uid' AND B.flag_sender>'1'
				)
			) AND (
				A.name LIKE '%" . $keyword . "%' OR
				A.mobile LIKE '%" . $keyword . "%' OR
				A.email LIKE '%" . $keyword . "%'
			)";
		if ($count > 0) {
			$db_query .= " LIMIT " . $count;
		}
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$ret[] = $db_row;
        	}
	}
	return $ret;
}

function phonebook_hook_phonebook_search_group($uid, $keyword = "", $count = 0) {
	$ret = array();
	$db_query = "
		SELECT DISTINCT id AS gpid, name AS group_name, code, flag_sender
		FROM " . _DB_PREF_ . "_featurePhonebook_group
		WHERE (
			uid='$uid' OR
			id in (
				SELECT B.id AS id FROM " . _DB_PREF_ . "_featurePhonebook AS A
				LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group_contacts AS C ON A.id=C.pid
				LEFT JOIN " . _DB_PREF_ . "_featurePhonebook_group AS B ON B.id=C.gpid
				WHERE A.mobile='" . user_getfieldbyuid($uid, 'mobile') . "' AND B.flag_sender='1'
			) OR (
			uid <>'$uid' AND flag_sender>'1'
			)
		)";
	if ($keyword) {
		$db_query .= " AND (
					name LIKE '%" . $keyword . "%' OR
					code LIKE '%" . $keyword . "%'
					)";
	}
	if ($count > 0) {
		$db_query .= " LIMIT " . $count;
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_search_user($keyword = "", $count = 0) {
	$keywords = $keyword;
	$fields = 'name, username';
	if ((int) $count) {
		$extras = 'LIMIT ' . (int) $count;
	}
	$ret = user_search($keywords, $fields, $extras);
	return $ret;
}

function phonebook_hook_webservices_output($operation, $requests) {
	global $user_config;
	if (!auth_isvalid()) {
		return FALSE;
	}
	$keyword = stripslashes($requests['keyword']);
	if (!$keyword) {
		$keyword = $requests['tag'];
	}
	if ($keyword && $user_config['uid']) {
		if (substr($keyword, 0, 1) == '@') {
			$keyword = substr($keyword, 1);
			$list = phonebook_search_user($keyword);
			foreach ($list as $data ) {
				$item[] = array(
					'id' => '@' . $data['username'],
					'text' => '@' . $data['name'] 
				);
			}
		} else if (substr($keyword, 0, 1) == '#') {
			$keyword = substr($keyword, 1);
			$list = phonebook_search_group($user_config['uid'], $keyword);
			foreach ($list as $data ) {
				$item[] = array(
					'id' => '#' . $data['code'],
					'text' => _('Group') . ': ' . $data['group_name'] . ' (' . $data['code'] . ')' 
				);
			}
		} else {
			$list = phonebook_search($user_config['uid'], $keyword);
			foreach ($list as $data ) {
				$item[] = array(
					'id' => $data['p_num'],
					'text' => $data['p_desc'] . ' (' . $data['p_num'] . ')' 
				);
			}
		}
	}
	if (count($item) == 0) {
		$item[] = array(
			'id' => $keyword,
			'text' => $keyword 
		);
	}
	$content = json_encode($item);
	ob_end_clean();
	header('Content-Type: text/json; charset=utf-8');
	$ret = $content;
	return $ret;
}
