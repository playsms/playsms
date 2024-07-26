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

function acl_getall()
{
	$ret = array(
		'0' => _('DEFAULT')
	);

	$conditions = array(
		'flag_deleted' => 0
	);
	$extras = array(
		'ORDER BY' => 'name'
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', '*', $conditions, [], $extras);
	foreach ( $list as $item ) {
		if ($item['id'] && trim($item['name'])) {
			$ret[$item['id']] = trim(strtoupper($item['name']));
		}
	}

	return $ret;
}

function acl_getallbyuid($uid)
{
	global $core_config;

	$ret = [];

	$acl_id = acl_getidbyuid($uid);
	$acl_name = acl_getname($acl_id);

	if (!$acl_name) {
		return FALSE;
	}

	$acl_subusers = explode(',', acl_getaclsubuser($acl_id));
	foreach ( $acl_subusers as $acl_subuser ) {
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

function acl_getdata($acl_id)
{
	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', '*', $conditions);
	$ret = $list[0];

	return $ret;
}

function acl_getname($acl_id)
{
	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'name', $conditions);
	$ret = trim($list[0]['name']) ? trim(strtoupper($list[0]['name'])) : _('DEFAULT');

	return $ret;
}

function acl_getid($acl_name)
{
	$ret = 0;
	if (!is_array($acl_name) && $acl_name) {
		$conditions = array(
			'name' => trim(strtoupper($acl_name)),
			'flag_deleted' => 0
		);
		$list = dba_search(_DB_PREF_ . '_tblACL', 'id', $conditions);
		$ret = (int) $list[0]['id'] ? (int) $list[0]['id'] : 0;
	}
	return $ret;
}

function acl_getaclsubuser($acl_id)
{
	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'acl_subuser', $conditions);
	$ret = trim(strtoupper($list[0]['acl_subuser']));

	return $ret;
}

function acl_geturl($acl_id)
{
	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'url', $conditions);
	$list[0]['url'] = isset($list[0]['url']) ? $list[0]['url'] : '';
	$urls = explode(',', $list[0]['url']);
	foreach ( $urls as $key => $val ) {
		if (trim($val)) {
			$ret[] = trim(htmlspecialchars_decode($val));
		}
	}

	return $ret;
}

function acl_isexists($acl_id)
{
	$conditions = array(
		'id' => (int) $acl_id,
		'flag_deleted' => 0
	);
	$list = dba_search(_DB_PREF_ . '_tblACL', 'name', $conditions);
	$list[0]['name'] = isset($list[0]['name']) ? $list[0]['name'] : '';
	$ret = (trim($list[0]['name']) ? TRUE : FALSE);

	return $ret;
}

function acl_getnamebyuid($uid)
{
	$acl_name = acl_getname(acl_getidbyuid($uid));

	return $acl_name;
}

function acl_getidbyuid($uid)
{
	$list = dba_search(
		_DB_PREF_ . '_tblUser',
		'acl_id',
		array(
			'flag_deleted' => 0,
			'uid' => $uid
		)
	);
	$acl_id = (int) $list[0]['acl_id'];
	if (!acl_isexists($acl_id)) {
		$acl_id = 0;
	}

	return $acl_id;
}

function acl_setbyuid($acl_id, $uid)
{
	$ret = FALSE;

	if ((int) $uid && $acl_name = acl_getname($acl_id)) {
		if (
			dba_update(
				_DB_PREF_ . '_tblUser',
				[
					'acl_id' => $acl_id
				],
				[
					'flag_deleted' => 0,
					'uid' => $uid
				]
			)
		) {
			return TRUE;
		}
	}

	return $ret;
}

/**
 * Check if current URI is in ACL
 *
 * @param string $url Current request URI
 * @param int $uid User ID
 * @return bool
 */
function acl_checkurl($url, $uid = 0)
{
	global $user_config, $core_config;

	// allowed if daemon process
	if ($core_config['daemon_process']) {

		return true;
	}

	// allowed if admin
	if (auth_isadmin()) {

		return true;
	}

	// allowed if part of always allowed URLs
	$always_allowed = [
		'app=ws',
		'app=webservice',
		'app=webservices',
		'app=main&inc=core_auth',
		'app=main&inc=core_welcome',
	];
	foreach ( $always_allowed as $always_url ) {
		$pos = strpos($url, $always_url);
		if ($pos !== false) {

			return true;
		}
	}

	$uid = (int) $uid ? (int) $uid : $user_config['uid'];
	$acl_id = acl_getidbyuid($uid);
	$data_acl = acl_getdata($acl_id);
	$acl_urls = acl_geturl($acl_id);

	// disallowed if URL is empty, or User ID not found or data is not complete
	if (!($url && $uid && $acl_id && is_array($data_acl) && is_array($acl_urls))) {

		return false;
	}

	// allowed if ACL URL is empty
	if (empty($acl_urls)) {

		return true;
	}

	sort($acl_urls, SORT_NATURAL | SORT_FLAG_CASE);

	foreach ( $acl_urls as $acl_url ) {

		// check exception
		$is_exception = false;
		if (substr($acl_url, 0, 1) == '!') {
			$acl_url = substr($acl_url, 1);
			$is_exception = true;
		}

		// check if ACL URL is part of URL
		$pos = strpos($url, $acl_url);

		// its an exception, invert the check result for this ACL URL
		if ($is_exception) {
			$pos = !$pos;
		}

		// the strpos result (after checking with exception) is considered TRUE
		if ($pos !== false) {

			return $data_acl['flag_disallowed'] ? false : true;
		}
	}

	return false;
}
