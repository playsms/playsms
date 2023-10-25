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

function phonebook_groupid2name($uid, $gpid)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_groupname2id($uid, $name)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_groupid2code($uid, $gpid)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_groupcode2id($uid, $code)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_getdatabynumber($uid, $mobile)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_number2id($uid, $p_num)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_number2name($uid, $p_num)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_number2email($uid, $p_num)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_number2tags($uid, $p_num)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_getmembercountbyid($gpid)
{
	$ret = core_call_hook();
	return $ret;
}

/**
 * Get members of a group, search by group ID
 *
 * @param int $gpid
 *        Group ID
 * @param string $orderby
 * @return array array(pid, p_desc, p_num, email)
 */
function phonebook_getdatabyid($gpid, $orderby = "")
{
	$ret = core_call_hook();
	return $ret;
}

/**
 * Get members of a group, search by User ID
 *
 * @param int $uid
 *        User ID
 * @param string $orderby
 * @return array array(pid, p_desc, p_num, email)
 */
function phonebook_getdatabyuid($uid, $orderby = "")
{
	$ret = core_call_hook();
	return $ret;
}

/**
 * Get data of a group, search by group ID
 *
 * @param int $gpid
 *        Group ID
 * @return array array(gpid, group_name, code, flag_sender)
 */
function phonebook_getgroupbyid($gpid)
{
	$ret = core_call_hook();
	return $ret;
}

/**
 * Get data of a group, search by User ID
 *
 * @param int $uid
 *        User ID
 * @param string $orderby
 * @return array array(gpid, group_name, code, flag_sender)
 */
function phonebook_getgroupbyuid($uid, $orderby = "")
{
	$ret = core_call_hook();
	return $ret;
}

/**
 * Search members, search by User ID and/or a keyword
 *
 * @param int $uid
 *        User ID
 * @param string $keyword
 *        Keyword
 * @param int $count
 *        Search limit
 * @return array array(pid, p_desc, p_num, email, tags)
 */
function phonebook_search($uid, $keyword = "", $count = 0)
{
	$ret = core_call_hook();
	return $ret;
}

/**
 * Search groups, search by User ID and/or a keyword
 *
 * @param int $uid
 *        User ID
 * @param string $keyword
 *        Keyword
 * @param int $count
 *        Search limit
 * @return array array(gpid, group_name, code, flag_sender)
 */
function phonebook_search_group($uid, $keyword = "", $count = 0)
{
	$ret = core_call_hook();
	return $ret;
}

/**
 * Search users, search by User ID and/or a keyword
 *
 * @param int $uid
 *        User ID
 * @param string $keyword
 *        Keyword
 * @param int $count
 *        Search limit
 * @return array Array of user's data
 */
function phonebook_search_user($uid, $keyword = "", $count = 0)
{
	$ret = core_call_hook();
	return $ret;
}

function phonebook_webservices_output($keyword, $items)
{
	global $core_config;

	$ret = array();
	$ret_final = array();

	// feature list
	for ($c = 0; $c < count($core_config['plugins']['list']['feature']); $c++) {
		$c_feature = $core_config['plugins']['list']['feature'][$c];
		$ret = core_hook(
			$c_feature,
			'phonebook_webservices_output',
			array(
				$keyword,
				$items
			)
		);
		if ($ret['modified']) {
			$items = ($ret['param']['items'] ? $ret['param']['items'] : $items);
			$ret_final['modified'] = $ret['modified'];
			$ret_final['cancel'] = $ret['cancel'];
			$ret_final['param']['items'] = $items;
		}
	}

	return $ret_final;
}

function phonebook_hook_webservices_output($operation, $requests, $returns)
{
	$items = array();

	$keyword = stripslashes($requests['keyword']);
	if (!$keyword) {
		$keyword = $requests['tag'];
	}

	if (!($operation == 'phonebook' && $keyword)) {
		return FALSE;
	}

	if (!auth_isvalid()) {
		return FALSE;
	}

	if ($returns['modified'] && $returns['param']['operation'] == 'phonebook' && isset($returns['param']['content'])) {
		$items = json_decode($returns['param']['content'], TRUE);
	}

	$ret = phonebook_webservices_output($keyword, $items);

	if ($ret['modified'] && isset($ret['param']['items'])) {
		$items = $ret['param']['items'];
	} else {
		$items[] = array(
			'id' => $keyword,
			'text' => $keyword
		);
	}

	$items = array_unique($items, SORT_REGULAR);

	$returns['modified'] = TRUE;
	$returns['param']['content'] = json_encode($items);

	if ($requests['debug'] == '1') {
		$returns['param']['content-type'] = "text/plain";
	}

	return $returns;
}