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
 * @param string $sender_id
 *        Sender ID
 * @return array User IDs
 */
function sender_id_owner($sender_id) {
	$ret = array();
	
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
	
	if ((int) $uid) {
		$search_items['uid'] = (int) $uid;
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
		if ($sender_id == $value) {
			return TRUE;
		}
	}
	
	return FALSE;
}

function sender_id_default_set($uid, $sender_id) {
	$db_table = _DB_PREF_ . '_tblUser';
	$items = array(
		'sender' => $sender_id 
	);
	$conditions = array(
		'flag_deleted' => 0,
		'uid' => $uid 
	);
	$ret = dba_update($db_table, $items, $conditions);
	
	return $ret;
}

function sender_id_default_get($uid) {
	$db_table = _DB_PREF_ . '_tblUser';
	$conditions = array(
		'flag_deleted' => 0,
		'uid' => $uid 
	);
	$data = dba_search($db_table, 'sender', $conditions);
	$sender_id = $data[0]['sender'];
	
	return $sender_id;
}

/**
 * Add sender ID
 *
 * @param integer $uid
 *        User ID
 * @param string $sender_id
 *        Sender ID
 * @param string $sender_id_description
 *        Sender ID description
 * @param integer $isdefault
 *        Flag 1 for default sender ID
 * @param integer $isapproved
 *        Flag 1 for approved sender ID
 * @return boolean TRUE when new sender ID has been added
 */
function sender_id_add($uid, $sender_id, $sender_id_description = '', $isdefault = 1, $isapproved = 1) {
	global $user_config;
	
	if (sender_id_check($uid, $sender_id)) {
		
		// not available
		return FALSE;
	} else {
		$default = (auth_isadmin() ? (int) $isdefault : 0);
		$approved = (auth_isadmin() ? (int) $isapproved : 0);
		
		$data_sender_id = array(
			$sender_id => $approved 
		);
		
		$sender_id_description = (trim($sender_id_description) ? trim($sender_id_description) : $sender_id);
		$data_description = array(
			$sender_id => $sender_id_description 
		);
		
		$uid = ((auth_isadmin() && $uid) ? $uid : $user_config['uid']);
		
		if ($uid) {
			registry_update($uid, 'features', 'sender_id', $data_sender_id);
			$ret = registry_update($uid, 'features', 'sender_id_desc', $data_description);
		} else {
			
			// unknown error
			return FALSE;
		}
		
		if ($ret[$sender_id]) {
			_log('sender ID has been added id:' . $sender_id . ' uid:' . $uid, 2, 'sender_id_add');
		} else {
			_log('fail to add sender ID id:' . $sender_id . ' uid:' . $uid, 2, 'sender_id_add');
			
			return FALSE;
		}
		
		// if default and approved
		if (auth_isadmin() && $default && $approved) {
			sender_id_default_set($uid, $sender_id);
		}
		
		// notify admins if user or subuser
		if ($user_config['status'] == 3 || $user_config['status'] == 4) {
			$admins = user_getallwithstatus(2);
			foreach ($admins as $admin) {
				$message_to_admins = sprintf(_('Username %s with account ID %d has requested approval for sender ID %s'), user_uid2username($uid), $uid, $sender_id);
				recvsms_inbox_add(core_get_datetime(), _SYSTEM_SENDER_ID_, $admin['username'], $message_to_admins);
			}
		}
		
		// added
		return TRUE;
	}
}

/**
 * Update sender ID
 *
 * @param integer $uid
 *        User ID
 * @param string $sender_id
 *        Sender ID
 * @param string $sender_id_description
 *        Sender ID description
 * @param integer $isdefault
 *        Flag 1 for default sender ID
 * @param integer $isapproved
 *        Flag 1 for approved sender ID
 * @return boolean TRUE when new sender ID has been updated
 */
function sender_id_update($uid, $sender_id, $sender_id_description = '', $isdefault = '_', $isapproved = '_') {
	global $user_config;
	
	if (sender_id_check($uid, $sender_id)) {
		$default = '_';
		if ($isdefault !== '_') {
			$default = ((int) $isdefault ? 1 : 0);
		}
		
		if ($isapproved !== '_') {
			if (auth_isadmin()) {
				$approved = ((int) $isapproved ? 1 : 0);
				$data_sender_id = array(
					$sender_id => $approved 
				);
			}
		}
		
		$sender_id_description = (trim($sender_id_description) ? trim($sender_id_description) : $sender_id);
		$data_description = array(
			$sender_id => $sender_id_description 
		);
		
		$uid = ((auth_isadmin() && $uid) ? $uid : $user_config['uid']);
		
		if ($uid) {
			if ($data_sender_id) {
				registry_update($uid, 'features', 'sender_id', $data_sender_id);
			}
			registry_update($uid, 'features', 'sender_id_desc', $data_description);
		} else {
			
			// unknown error
			return FALSE;
		}
		
		// set default
		if ($default !== '_') {
			if (auth_isadmin() && $default && $approved) {
				
				// set default if isadmin, default and approved
				sender_id_default_set($uid, $sender_id);
			} else {
				
				// set to empty (remove default)
				sender_id_default_set($uid, '');
			}
		}
		
		return TRUE;
	} else {
		
		// not found
		return FALSE;
	}
}
