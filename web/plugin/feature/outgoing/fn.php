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

function outgoing_getdata() {
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureOutgoing ORDER BY dst";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
	}
	
	return $ret;
}

function outgoing_getdst($id) {
	if ($id) {
		$db_query = "SELECT dst FROM " . _DB_PREF_ . "_featureOutgoing WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$dst = $db_row['dst'];
	}
	
	return $dst;
}

function outgoing_getprefix($id) {
	if ($id) {
		$db_query = "SELECT prefix FROM " . _DB_PREF_ . "_featureOutgoing WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$prefix = $db_row['prefix'];
		$prefix = substr($prefix, 0, 8);
	}
	
	return $prefix;
}

function outgoing_getgateway($id) {
	if ($id) {
		$db_query = "SELECT gateway FROM " . _DB_PREF_ . "_featureOutgoing WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$gateway = $db_row['gateway'];
	}
	
	return $gateway;
}

function outgoing_prefix2gateway($prefix) {
	if ($prefix) {
		$prefix = substr($prefix, 0, 8);
		$db_query = "SELECT gateway FROM " . _DB_PREF_ . "_featureOutgoing WHERE prefix='$prefix'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$gateway = $db_row['gateway'];
	}
	
	return $gateway;
}

function outgoing_mobile2gateway($mobile) {
	$mobile = core_sanitize_numeric($mobile);
	
	if (strlen($mobile) < 8) {
		$prefix = substr($mobile, 0, strlen($mobile));
	} else {
		$prefix = substr($mobile, 0, 8);
	}
	
	for($i = strlen($prefix); $i > 0; $i--) {
		$c_prefix = substr($mobile, 0, $i);
		if ($gateway = outgoing_prefix2gateway($c_prefix)) {
			$ret = $gateway;
			break;
		}
	}
	
	return $ret;
}

function outgoing_hook_sendsms_intercept($sms_sender, $sms_footer, $sms_to, $sms_msg, $uid, $gpid, $sms_type, $unicode, $gw) {
	$ret = array();
	
	if ($gw) {
		_log('using supplied gateway gw:[' . $gw . ']', 3, 'outgoing_hook_sendsms_intercept');
	} else if ($gw = outgoing_mobile2gateway($sms_to)) {
		_log('using prefix based gateway gw:[' . $gw . ']', 3, 'outgoing_hook_sendsms_intercept');
	} else {
		_log('no route found', 3, 'outgoing_hook_sendsms_intercept');
	}
	
	if ($gw) {
		$ret['modified'] = TRUE;
		$ret['param']['gw'] = $gw;
	}
	return $ret;
}
