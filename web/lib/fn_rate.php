<?php
defined('_SECURE_') or die('Forbidden');

function rate_setusercredit($uid, $remaining=0) {
	global $core_config;
	$ok = false;
	if ($uid) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if (x_hook($core_config['toolslist'][$c],'rate_setusercredit',array($uid,$remaining))) {
				$ok = true;
				break;
			}
		}
	}
	return $ok;
}

function rate_getusercredit($username) {
	global $core_config;
	if ($username) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if ($credit = x_hook($core_config['toolslist'][$c],'rate_getusercredit',array($username))) {
				break;
			}
		}
	}
	$credit = ( $credit ? $credit : 0 );
	return $credit;
}

function rate_cansend($username, $sms_to) {
	global $core_config;
	$ok = false;
	if ($username) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if (x_hook($core_config['toolslist'][$c],'rate_cansend',array($username,$sms_to))) {
				$ok = true;
				break;
			}
		}
	}
	return $ok;
}

function rate_deduct($smslog_id) {
	global $core_config;
	$ok = false;
	if ($smslog_id) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if (x_hook($core_config['toolslist'][$c],'rate_deduct',array($smslog_id))) {
				$ok = true;
				break;
			}
		}
	}
	return $ok;
}

function rate_refund($smslog_id) {
	global $core_config;
	$ok = false;
	if ($smslog_id) {
		for ($c=0;$c<count($core_config['toolslist']);$c++) {
			if (x_hook($core_config['toolslist'][$c],'rate_refund',array($smslog_id))) {
				$ok = true;
				break;
			}
		}
	}
	return $ok;
}

?>