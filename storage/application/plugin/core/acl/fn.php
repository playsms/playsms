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
	$conditions = array(
		'flag_deleted' => 0 
	);
	$extras = array(
		'ORDER BY' => 'name' 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', '*', $conditions, '', $extras);
	foreach ($list as $item) {
		if ($item['id'] && trim($item['name'])) {
			$ret[$item['id']] = trim(strtoupper($item['name']));
		}
	}
	
	return $ret;
}

function acl_getallbyuid($uid) {
	global $core_config;
	
	$acl_id = acl_getidbyuid($uid);
	$acl_name = acl_getname($acl_id);
	
	if (!$acl_name) {
		return FALSE;
	}
	
	$acl_subusers = explode(',', acl_getaclsubuser($acl_id));
	foreach ($acl_subusers as $acl_subuser) {
		$acl_subuser = trim($acl_subuser);
		$c_acl_id = acl_getid($acl_subuser);
		
		if ($c_acl_id && $acl_subuser) {
			$ret[$c_acl_id] = $acl_subuser;
		}
	}
	
	if (!count($ret)) {
		$default_acl_id = ($core_config['main']['default_acl'] ? $core_config['main']['default_acl'] : 0);
		$default_acl_name = acl_getname($default_acl_id);
		$ret = array(
			$default_acl_id => $default_acl_name 
		);
	}
	
	return $ret;
}

function acl_getdata($acl_id) {
	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', '*', $conditions);
	$ret = $list[0];
	
	return $ret;
}

function acl_getname($acl_id) {
	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'name', $conditions);
	$ret = (trim($list[0]['name']) ? trim(strtoupper($list[0]['name'])) : _('DEFAULT'));
	
	return $ret;
}

function acl_getid($acl_name) {
	$conditions = array(
		'name' => trim(strtoupper($acl_name)),
		'flag_deleted' => 0 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'id', $conditions);
	$ret = ((int) $list[0]['id'] ? (int) $list[0]['id'] : 0);
	
	return $ret;
}

function acl_getaclsubuser($acl_id) {
	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'acl_subuser', $conditions);
	$ret = trim(strtoupper($list[0]['acl_subuser']));
	
	return $ret;
}

function acl_geturl($acl_id) {
	$ret = [];

	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'url', $conditions);
	$urls = explode(',', $list[0]['url']);
	foreach ($urls as $key => $val) {
		if (trim($val)) {
			$ret[] = trim(htmlspecialchars_decode($val));
		}
	}
	
	return $ret;
}

function acl_isexists($acl_id) {
	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0 
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'name', $conditions);
	$ret = (trim($list[0]['name']) ? TRUE : FALSE);
	
	return $ret;
}

function acl_getnamebyuid($uid) {
	$acl_name = acl_getname(acl_getidbyuid($uid));
	
	return $acl_name;
}

function acl_getidbyuid($uid) {
	$list = dba_search(_DB_PREF_ . '_tblUser', 'acl_id', array(
		'flag_deleted' => 0,
		'uid' => $uid 
	));
	$acl_id = (int) $list[0]['acl_id'];
	if (!acl_isexists($acl_id)) {
		$acl_id = 0;
	}
	
	return $acl_id;
}

function acl_setbyuid($acl_id, $uid) {
	$ret = FALSE;
	
	if ((int) $uid && $acl_name = acl_getname($acl_id)) {
		if (dba_update(_DB_PREF_ . '_tblUser', array(
			'acl_id' => $acl_id 
		), array(
			'flag_deleted' => 0,
			'uid' => $uid 
		))) {
			return TRUE;
		}
	}
	
	return $ret;
}

function acl_checkurl($url, $uid = NULL) {
	// $uid must exists
	$uid = ((int) $uid ? (int) $uid : $_SESSION['uid']);
	$acl_id = acl_getidbyuid($uid);
	$data_acl = acl_getdata($acl_id);

	// get rules
	$acl_urls = acl_geturl($acl_id);
	if (!$data_acl['flag_disallowed']) {
		$acl_urls[] = 'app=ws';
		$acl_urls[] = 'app=webservice';
		$acl_urls[] = 'app=webservices';
		$acl_urls[] = 'inc=core_auth';
		$acl_urls[] = 'inc=core_welcome';
	}
	sort($acl_urls, SORT_NATURAL | SORT_FLAG_CASE);

	// true if admin, false if $uid=0 but not admin
	if ($_SESSION['status'] == 2) {

		return TRUE;
	}

	// false if no input $url
	$input = [];
	if ($url) {
		if (is_array($url)) {
			$input = $url;
		} else {
			$url = parse_url($url, PHP_URL_QUERY);
			parse_str($url, $input);
		}
	} else {

		return TRUE;
	}
	$input = _acl_sanitize($input);

	foreach ($acl_urls as $acl_url) {
		// looking for exception rule (prefixed with punctuation)
		// if found then clean $acl_url from exception marker (remove punctuation)
		$is_exception = _acl_isexception($acl_url);

		// turn url into array
		$acl = [];
		parse_str($acl_url, $acl);
		$acl = _acl_sanitize($acl);

		$found_match = FALSE;

		// if the rule not match with input url then flip to FALSE
		if ($acl == array_intersect_assoc($acl, $input)) {
			// flip if an exception rule
			if ($is_exception) {
				if ($data_acl['flag_disallowed']) {

					return TRUE;
				}

				return FALSE;
			}

			// if we found match and ACL is disallowed url then FALSE
			if ($data_acl['flag_disallowed']) {

				return FALSE;
			}

			return TRUE;
		}
	}

	if ($data_acl['flag_disallowed']) {

		return TRUE;
	} else {

		return FALSE;
	}
}

function _acl_sanitize($data_raw = array()) {
	$data = [];

	foreach ($data_raw as $key => $val) {
		if ($key && !is_array($val)) {
			$key = strtolower(trim($key));
			$val = strtolower(trim($val));
			if ((strlen($key) <= 40) && (strlen($val) <= 40)) {
				$data[$key] = $val;
			}
		}
	}

	if ($data) {
		$data = array_unique($data);
		ksort($data);
	}

	return $data;
}

function _acl_isexception(&$url) {
	$url = trim($url);
	if (substr($url, 0, 1) == '!') {
		$url = substr($url, 1);
		
		return TRUE;
	}

	return FALSE;
}
