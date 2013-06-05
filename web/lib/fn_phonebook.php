<?php
defined('_SECURE_') or die('Forbidden');

function phonebook_groupid2name($gpid) {
	global $core_config;
	if ($gpid) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if ($gp_name = x_hook($core_config['toolslist'][$c],'phonebook_groupid2name',array($gpid))) {
				break;
			}
		}
	}
	return $gp_name;
}

function phonebook_groupname2id($uid,$gp_name) {
	global $core_config;
	if ($uid && $gp_name) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if ($gpid = x_hook($core_config['toolslist'][$c],'phonebook_groupname2id',array($uid,$gp_name))) {
				break;
			}
		}
	}
	return $gpid;
}

function phonebook_groupid2code($gpid) {
	global $core_config;
	if ($gpid) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if ($gp_code = x_hook($core_config['toolslist'][$c],'phonebook_groupid2code',array($gpid))) {
				break;
			}
		}
	}
	return $gp_code;
}

function phonebook_groupcode2id($uid,$gp_code) {
	global $core_config;
	if ($uid && $gp_code) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if ($gpid = x_hook($core_config['toolslist'][$c],'phonebook_groupcode2id',array($uid,$gp_code))) {
				break;
			}
		}
	}
	return $gpid;
}

function phonebook_number2name($p_num, $c_username="") {
	global $core_config;
	if ($p_num) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if ($p_desc = x_hook($core_config['toolslist'][$c],'phonebook_number2name',array($p_num,$c_username))) {
				break;
			}
		}
	}
	return $p_desc;
}

function phonebook_getmembercountbyid($gpid) {
	global $core_config;
	$count = 0;
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($count = x_hook($core_config['toolslist'][$c],'phonebook_getmembercountbyid',array($gpid))) {
			break;
		}
	}
	return $count;
}

function phonebook_getdatabyid($gpid, $orderby="") {
	global $core_config;
	$ret = array();
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($ret = x_hook($core_config['toolslist'][$c],'phonebook_getdatabyid',array($gpid,$orderby))) {
			break;
		}
	}
	return $ret;
}

function phonebook_getdatabyuid($uid, $orderby="") {
	global $core_config;
	$ret = array();
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($ret = x_hook($core_config['toolslist'][$c],'phonebook_getdatabyuid',array($uid,$orderby))) {
			break;
		}
	}
	return $ret;
}

function phonebook_getsharedgroup($uid) {
	global $core_config;
	$ret = array();
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($ret = x_hook($core_config['toolslist'][$c],'phonebook_getsharedgroup',array($uid))) {
			break;
		}
	}
	return $ret;
}

function phonebook_getgroupbyuid($uid, $orderby="") {
	global $core_config;
	$ret = array();
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($ret = x_hook($core_config['toolslist'][$c],'phonebook_getgroupbyuid',array($uid,$orderby))) {
			break;
		}
	}
	return $ret;
}

function phonebook_search($uid, $keyword="", $count="") {
	global $core_config;
	$ret = array();
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($ret = x_hook($core_config['toolslist'][$c],'phonebook_search',array($uid,$keyword,$count))) {
			break;
		}
	}
	return $ret;
}

function phonebook_search_group($uid, $keyword="", $count="") {
	global $core_config;
	$ret = array();
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if ($ret = x_hook($core_config['toolslist'][$c],'phonebook_search_group',array($uid,$keyword,$count))) {
			break;
		}
	}
	return $ret;
}

?>