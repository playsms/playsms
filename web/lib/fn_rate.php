<?php

function rate_getbyprefix($p_dst) {
    global $core_config;
    $rate = 0;
    if ($p_dst) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if ($rate = x_hook($core_config['toolslist'][$c],'rate_getbyprefix',array($p_dst))) {
		break;
	    }
	}
    }
    return $rate;
}

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

function rate_getusercredit($username)
{
    global $core_config;
    $credit = 0;
    if ($username) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if ($credit = x_hook($core_config['toolslist'][$c],'rate_getusercredit',array($username))) {
		break;
	    }
	}
    }
    return $credit;
}

function rate_getmax($default="") {
    global $core_config;
    $rate = 0;
    if ($username) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if ($rate = x_hook($core_config['toolslist'][$c],'rate_getmax',array($default))) {
		break;
	    }
	}
    }
    return $rate;
}

function rate_cansend($username, $default="") {
    global $core_config;
    $ok = false;
    if ($username) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if (x_hook($core_config['toolslist'][$c],'rate_cansend',array($username,$default))) {
		$ok = true;
		break;
	    }
	}
    }
    return $ok;
}

function rate_setcredit($smslog_id) {
    global $core_config;
    $ok = false;
    if ($username) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if (x_hook($core_config['toolslist'][$c],'rate_setcredit',array($smslog_id))) {
		$ok = true;
		break;
	    }
	}
    }
    return $ok;
}

?>