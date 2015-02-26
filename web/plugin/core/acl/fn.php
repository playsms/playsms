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

function acl_getall() {
	$ret = array(
		'0' => _('Default ACL') 
	);
	
	$extras = array(
		'ORDER BY' => 'name' 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', '*', '', '', $extras);
	foreach ($list as $item) {
		if ($item['id'] && $item['name']) {
			$ret[$item['id']] = $item['name'];
		}
	}
	
	return $ret;
}

function acl_getdata($acl_id) {
	$conditions = array(
		'id' => (int) $acl_id 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', '*', $conditions);
	$ret = $list[0];
	
	return $ret;
}

function acl_getname($acl_id) {
	$conditions = array(
		'id' => (int) $acl_id 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'name', $conditions);
	$ret = (trim($list[0]['name']) ? trim($list[0]['name']) : _('Default ACL'));
	
	return $ret;
}

function acl_geturl($acl_id) {
	$ret = array(
		'inc=core_auth',
		'inc=core_welcome' 
	);
	
	$conditions = array(
		'id' => (int) $acl_id 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'url', $conditions);
	$urls = explode(',', $list[0]['url']);
	foreach ($urls as $key => $val) {
		if (trim($val)) {
			$ret[] = trim($val);
		}
	}
	
	return $ret;
}

function acl_getnamebyuid($uid) {
	$acl_name = acl_getname(acl_getidbyuid($uid));
	
	return $acl_name;
}

function acl_getidbyuid($uid) {
	$list = dba_search(_DB_PREF_ . '_tblUser', 'acl_id', array(
		'uid' => $uid 
	));
	$acl_id = (int) $list[0]['acl_id'];
	
	return $acl_id;
}

function acl_setbyuid($acl_id, $uid) {
	$ret = FALSE;
	
	if ((int) $uid && $acl_name = acl_getname($acl_id)) {
		if (dba_update(_DB_PREF_ . '_tblUser', array(
			'acl_id' => $acl_id 
		), array(
			'uid' => $uid 
		))) {
			return TRUE;
		}
	}
	
	return $ret;
}

function acl_checkurl($url, $uid = 0) {
	global $user_config, $core_config;
	
	$uid = ((int) $uid ? (int) $uid : $user_config['uid']);
	if (!$core_config['daemon_process'] && $url && $uid && ($acl_id = acl_getidbyuid($uid))) {
		$acl_urls = acl_geturl($acl_id);
		foreach ($acl_urls as $acl_url) {
			$pos = strpos($url, $acl_url);
			if ($pos !== FALSE) {
				return TRUE;
			}
		}
	} else {
		return TRUE;
	}
	
	return FALSE;
}
