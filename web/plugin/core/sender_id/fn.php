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

function sender_id_description($uid, $sender_id) {
	global $user_config;
	
	$search = array(
		'uid' => $uid,
		'registry_family' => 'sender_id_desc',
		'registry_key' => core_sanitize_sender($sender_id) 
	);
	foreach (registry_search_record($search) as $desc) {
		$ret = $desc['registry_value'];
	}
	return $ret;
}

function sender_id_check($uid, $sender_id) {
	global $user_config;
	
	$ret = FALSE;
	$condition = array(
		'uid' => $uid,
		'registry_family' => 'sender_id',
		'registry_key' => core_sanitize_sender($sender_id) 
	);
	if (registry_search_record($condition)) {
		$ret = TRUE;
	}
	
	return $ret;
}

/**
 * Get owner of sender ID
 * 
 * @param string $sender_id Sender ID
 * @return array User IDs
 */
function sender_id_owner($sender_id) {
	$ret = 0;
	
	$condition = array(
		'registry_family' => 'sender_id',
		'registry_key' => core_sanitize_sender($sender_id) 
	);
	$list = registry_search_record($condition);
	foreach ($list as $data) {
		if ($data['uid']) {
			$ret[] = $data['uid'];
		}
	}
	
	return $ret;
}

function sender_id_search($uid = 0) {
	$search_items['registry_family'] = 'sender_id';
	
	if ((int)$uid) {
		$search_items['uid'] = (int)$uid;
	}
	
	foreach (registry_search_record($search_items, '', array(
		'ORDER BY' => 'c_timestamp DESC, uid' 
	)) as $sender_id) {
		
		// show only approved sender_id
		if ($sender_id['registry_value'] == 1) {
			$ret[] = core_sanitize_sender($sender_id['registry_key']);
		}
	}
	
	return $ret;
}

function sender_id_getall($username = '') {
	$ret = array();
	
	if ($username) {
		$uid = user_username2uid($username);
	} else {
		$uid = 0;
	}
	
	foreach (sender_id_search($uid) as $value) {
		$ret[] = $value;
	}
	
	return $ret;
}

function sender_id_isvalid($username, $sender_id) {
	$uid = user_username2uid($username);
	
	foreach (sender_id_search($uid) as $value) {
		if ($sender_id == $value) {return TRUE;}
	}
	
	return FALSE;
}

function sender_id_default_set($uid, $sender_id) {
	$db_table = _DB_PREF_ . '_tblUser';
	$items = array(
		'sender' => $sender_id 
	);
	$conditions = array(
		'uid' => $uid 
	);
	$ret = dba_update($db_table, $items, $conditions);
	
	return $ret;
}

function sender_id_default_get($uid) {
	$db_table = _DB_PREF_ . '_tblUser';
	$conditions = array(
		'uid' => $uid 
	);
	$data = dba_search($db_table, 'sender', $conditions);
	$sender_id = $data[0]['sender'];
	
	return $sender_id;
}
