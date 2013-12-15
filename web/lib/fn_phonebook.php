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

function phonebook_groupid2name($gpid) {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_groupname2id($uid,$gp_name) {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_groupid2code($gpid) {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_groupcode2id($uid,$gp_code) {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_number2name($p_num, $c_username="") {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_getmembercountbyid($gpid) {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Get members of a group, search by group ID
 * @param integer $gpid group ID
 * @param string $orderby field name
 * @return array array(id, p_desc, p_num)
 */
function phonebook_getdatabyid($gpid, $orderby="") {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_getdatabyuid($uid, $orderby="") {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_getsharedgroup($uid) {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_getgroupbyuid($uid, $orderby="") {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_search($uid, $keyword="", $count="") {
	$ret = core_call_hook();
	return $ret;
}

function phonebook_search_group($uid, $keyword="", $count="") {
	$ret = core_call_hook();
	return $ret;
}
