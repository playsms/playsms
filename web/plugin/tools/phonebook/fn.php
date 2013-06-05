<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php

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
		$c_uid = username2uid($c_username);
		$uid = $c_uid ? $c_uid : $core_config['user']['uid'];
		// remove +
		$mobile = str_replace('+','',$mobile);
		// remove first 3 digits if phone number length more than 7
		if (strlen($mobile) > 7) { $mobile = substr($mobile,3); }
		$db_query = "
			SELECT A.name AS name FROM "._DB_PREF_."_toolsPhonebook AS A
			INNER JOIN "._DB_PREF_."_toolsPhonebook_group AS B ON A.gpid=B.id
			WHERE mobile LIKE '%".$mobile."' AND B.uid='$uid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$name = $db_row['name'];
		if (! $name) {
			$ret = phonebook_getsharedgroup($uid);
			for ($i=0;$i<count($ret);$i++) {
				$c_gpid = $ret[$i]['gpid'];
				$db_query = "SELECT name FROM "._DB_PREF_."_toolsPhonebook WHERE mobile LIKE '%".$mobile."' AND gpid='$c_gpid'";
				$db_result = dba_query($db_query);
				$db_row = dba_fetch_array($db_result);
				if ($name = $db_row['name']) {
					break;
				}
			}
		}
	}
	return $name;
}

function phonebook_hook_phonebook_getmembercountbyid($gpid) {
	$count = 0;
	$db_query = "SELECT COUNT(*) as count FROM "._DB_PREF_."_toolsPhonebook WHERE gpid='$gpid'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$count = ( $db_row['count'] ? $db_row['count'] : 0 );
	}
	return $count;
}

function phonebook_hook_phonebook_getdatabyid($gpid, $orderby="") {
	$ret = array();
	$db_query = "SELECT id, name AS p_desc, mobile AS p_num FROM "._DB_PREF_."_toolsPhonebook WHERE gpid='$gpid'";
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
		SELECT A.id AS pid, gpid, A.name AS p_desc, mobile AS p_num, email, B.name AS group_name, code 
		FROM "._DB_PREF_."_toolsPhonebook AS A
		INNER JOIN "._DB_PREF_."_toolsPhonebook_group AS B ON A.gpid=B.id
		WHERE mobile LIKE '%".$mobile."' AND B.uid='$uid'";
	if ($orderby) {
		$db_query .= " ORDER BY ".$orderby;
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_getgroupbyuid($uid, $orderby="") {
	$ret = array();
	$db_query = "SELECT id AS gpid, name AS gp_name, code AS gp_code FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid'";
	if ($orderby) {
		$db_query .= " ORDER BY ".$orderby;
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	return $ret;
}

function phonebook_hook_phonebook_search($uid, $keyword="", $count="") {
	$ret = array();
	if ($keyword) {
		$fields = 'A.id AS pid,gpid,A.name AS p_desc,A.mobile AS p_num,email,B.name AS group_name,code';
		$conditions = array('A.uid' => $uid);
		$keywords = array('A.name' => '%'.$keyword.'%', 'A.mobile' => '%'.$keyword.'%', 'A.email' => '%'.$keyword.'%');
		if ((int) $count) {
			$extras = array('LIMIT' => $count);
		}
		$join = "INNER JOIN "._DB_PREF_."_toolsPhonebook_group AS B ON A.gpid=B.id";
		$ret = dba_search(_DB_PREF_.'_toolsPhonebook AS A', $fields, $conditions, $keywords, $extras, $join);
	}
	return $ret;
}

function phonebook_hook_phonebook_search_group($uid, $keyword="", $count="") {
	$ret = array();
	if ($keyword) {
		$fields = 'id AS gpid,name AS group_name,code';
		$conditions = array('uid' => $uid);
		$keywords = array('name' => '%'.$keyword.'%', 'code' => '%'.$keyword.'%');
		if ((int) $count) {
			$extras = array('LIMIT' => $count);
		}
		$ret = dba_search(_DB_PREF_.'_toolsPhonebook_group', $fields, $conditions, $keywords, $extras);
	}
	return $ret;
}

?>