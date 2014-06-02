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

function sender_id_list() {
	global $user_config, $icon_config;
	
	$ret = array();
	
	$sender_search = array(
		'uid' => $user_config['uid'],
		'registry_family' => 'sender_id'
	);
	if (auth_isadmin()) {
		unset($sender_search['uid']);
	}
	foreach (registry_search_record($sender_search, '', array(
		'ORDER BY' => 'c_timestamp DESC, uid'
	)) as $sender_id) {
		$username = (auth_isadmin() ? user_uid2username($sender_id['uid']) : '');
		$status = (($sender_id['registry_value'] == 1) ? "<span class=status_enabled></span>" : "<span class=status_disabled></span>");
		$toggle_status = ((auth_isadmin()) ? "<a href='" . _u('index.php?app=main&inc=feature_sender_id&op=toggle_status&id=' . $sender_id['id']) . "'>" . $status . "</a>" : $status);
		$action = "
			<a href='" . _u('index.php?app=main&inc=feature_sender_id&op=sender_id_edit&id=' . $sender_id['id']) . "'>" . $icon_config['edit'] . "</a>
			<a href=\"javascript: ConfirmURL('" . addslashes(_('Are you sure you want to delete sender ID') . ' ? (' . _('Sender ID') . ': ' . $sender_id['registry_key'] . ')') . "','" . _u('index.php?app=main&inc=feature_sender_id&op=sender_id_delete&id=' . $sender_id['id']) . "')\">" . $icon_config['delete'] . "</a>
		";
		$ret[] = array(
			'username' => $username,
			'sender_id' => core_sanitize_sender($sender_id['registry_key']) ,
			'sender_id_description' => sender_id_description($sender_id['registry_key']) ,
			'created' => core_convert_datetime($sender_id['c_timestamp']) ,
			'status' => $toggle_status,
			'action' => $action,
		);
	}
	return $ret;
}

function sender_id_description($sender_id) {
	global $user_config;
	
	$search = array(
		'uid' => $user_config['uid'],
		'registry_family' => 'sender_id_desc',
		'registry_key' => core_sanitize_sender($sender_id) ,
	);
	if (auth_isadmin()) {
		unset($search['uid']);
	}
	foreach (registry_search_record($search) as $desc) {
		$ret = $desc['registry_value'];
	}
	return $ret;
}

function sender_id_check($sender_id) {
	global $user_config;
	
	$ret = FALSE;
	$condition = array(
		'uid' => $user_config['uid'],
		'registry_family' => 'sender_id',
		'registry_key' => core_sanitize_sender($sender_id) ,
	);
	if (registry_search_record($condition)) {
		$ret = TRUE;
	}
	
	return $ret;
}

function sender_id_search($uid) {
	$sender_search = array(
		'uid' => $uid,
		'registry_family' => 'sender_id'
	);
	foreach (registry_search_record($sender_search, '', array(
		'ORDER BY' => 'c_timestamp DESC, uid'
	)) as $sender_id) {

		//show only approved sender_id
		if ($sender_id['registry_value'] == 1) {
			$ret[] = core_sanitize_sender($sender_id['registry_key']);
		}
	}
	
	return $ret;
}

function sender_id_hook_sendsms_getall_sender($username) {
	$ret = array();
	
	$uid = user_username2uid($username);
	
	foreach (sender_id_search($uid) as $value) {
		$ret[] = $value;
	}
	
	return $ret;
}

function sender_id_hook_sendsms_sender_isvalid($username, $sender_id) {
	$uid = user_username2uid($username);
	
	foreach (sender_id_search($uid) as $value) {
		if ($sender_id == $value) {
			return TRUE;
		}
	}
	
	return FALSE;
}
