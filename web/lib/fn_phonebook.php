<?php
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

?>