<?php
defined('_SECURE_') or die('Forbidden');

function rate_setusercredit($uid, $remaining=0) {
	$ret = core_call_hook();
	return $ret;
}

function rate_getusercredit($username) {
	$ret = core_call_hook();
	return $ret;
}

function rate_cansend($username, $sms_to) {
	$ret = core_call_hook();
	return $ret;
}

function rate_deduct($smslog_id) {
	$ret = core_call_hook();
	return $ret;
}

function rate_refund($smslog_id) {
	$ret = core_call_hook();
	return $ret;
}

?>