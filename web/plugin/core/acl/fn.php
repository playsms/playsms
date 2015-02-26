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

function acl_getdata($acl) {
	$conditions = array(
		'id' => (int) $acl 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', '*', $conditions);
	$ret = $list[0];
	
	return $ret;
}

function acl_getname($acl) {
	$conditions = array(
		'id' => (int) $acl 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'name', $conditions);
	$ret = (trim($list[0]['name']) ? trim($list[0]['name']) : _('Default ACL'));
	
	return $ret;
}

function acl_geturl($acl) {
	$ret = array(
		'inc=core_auth',
		'inc=core_welcome' 
	);
	
	$conditions = array(
		'id' => (int) $acl 
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

function acl_uid2name($uid) {
	$data = registry_search($uid, 'core', 'user_config');
	$ret = acl_getname($data['core']['user_config']['acl']);
	
	return $ret;
}

function acl_uid2id($uid) {
	$data = registry_search($uid, 'core', 'user_config');
	$ret = (int) $data['core']['user_config']['acl'];
	
	return $ret;
}

function acl_checkurl($url, $uid = 0) {
	global $user_config, $core_config;
	
	$uid = ((int) $uid ? (int) $uid : $user_config['uid']);
	if (!$core_config['daemon_process'] && $url && $uid && ($acl = acl_uid2id($uid))) {
		$acl_urls = acl_geturl($acl);
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
