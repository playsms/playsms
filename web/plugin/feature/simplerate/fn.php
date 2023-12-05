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

function simplerate_getdst($id)
{
	$dst = '';

	if ($id) {
		$db_query = "SELECT dst FROM " . _DB_PREF_ . "_featureSimplerate WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$dst = $db_row['dst'];
	}

	return $dst;
}

function simplerate_getprefix($id)
{
	$prefix = '';

	if ($id) {
		$db_query = "SELECT prefix FROM " . _DB_PREF_ . "_featureSimplerate WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$prefix = $db_row['prefix'];
	}

	return $prefix;
}

function simplerate_getbyid($id)
{
	if ($id) {
		$db_query = "SELECT rate FROM " . _DB_PREF_ . "_featureSimplerate WHERE id='$id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$rate = $db_row['rate'];
	}

	$rate = (($rate > 0) ? $rate : 0);

	return $rate;
}

function simplerate_getadhoccredit($uid)
{
	$balance = 0;

	if ($c_uid = (int) $uid) {
		$db_query = "SELECT adhoc_credit FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND uid='$c_uid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$balance = (float) $db_row['adhoc_credit'];
	}

	return $balance;
}

function simplerate_setadhoccredit($uid, $balance)
{
	$balance = (float) $balance;

	if ($c_uid = (int) $uid) {
		$db_query = "UPDATE " . _DB_PREF_ . "_tblUser SET c_timestamp='" . time() . "', adhoc_credit='$balance' WHERE uid='$c_uid' AND flag_deleted='0'";
		dba_query($db_query);
	}
}

// -----------------------------------------------------------------------------------------
function simplerate_hook_rate_getbyprefix($sms_to)
{
	global $core_config;

	$found = FALSE;
	$default_rate = ($core_config['main']['default_rate'] > 0 ? $core_config['main']['default_rate'] : 0);
	$rate = $default_rate;
	$prefix = preg_replace('/[^0-9.]*/', '', $sms_to);
	$m = (strlen($prefix) > 10 ? 10 : strlen($prefix));
	for ($i = $m + 1; $i > 0; $i--) {
		$prefix = substr($prefix, 0, $i);
		$db_query = "SELECT id,dst,prefix,rate FROM " . _DB_PREF_ . "_featureSimplerate WHERE prefix='$prefix'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($db_row['id']) {
			$rate = $db_row['rate'];
			$found = TRUE;
			break;
		}
	}

	if ($found) {
		_log("found rate id:" . $db_row['id'] . " prefix:" . $db_row['prefix'] . " rate:" . $rate . " description:" . $db_row['dst'] . " to:" . $sms_to, 3, "simplerate_hook_rate_getbyprefix");
	} else {
		_log("rate not found to:" . $sms_to . " default_rate:" . $default_rate, 3, "simplerate_hook_rate_getbyprefix");
	}

	$rate = (($rate > 0) ? $rate : 0);

	return $rate;
}

function simplerate_hook_rate_getcharges($uid, $sms_len, $unicode, $sms_to)
{
	global $user_config;

	// default length per SMS
	$length = ($unicode ? 70 : 160);

	// connector pdu length
	$minus = ($unicode ? 3 : 7);

	// count unicodes as normal SMS
	$user = user_getdatabyuid($uid);
	if ($unicode && $user['opt']['enable_credit_unicode']) {
		$length = 140;
	}

	// get sms count
	$count = 1;
	if ($sms_len > $length) {
		$count = ceil($sms_len / ($length - $minus));
	}

	// calculate charges
	$rate = simplerate_hook_rate_getbyprefix($sms_to);
	$charge = $count * $rate;

	_log('uid:' . $uid . ' u:' . $user['username'] . ' len:' . $sms_len . ' unicode:' . $unicode . ' to:' . $sms_to . ' enable_credit_unicode:' . (int) $user['opt']['enable_credit_unicode'] . ' count:' . $count . ' rate:' . $rate . ' charge:' . $charge, 3, 'simplerate_hook_rate_getcharges');

	return array(
		$count,
		$rate,
		$charge
	);
}

function simplerate_hook_rate_cansend($username, $sms_len, $unicode, $sms_to)
{
	global $core_config;

	$uid = user_username2uid($username);
	list($count, $rate, $charge) = simplerate_hook_rate_getcharges($uid, $sms_len, $unicode, $sms_to);

	// sender's
	$adhoc_credit = simplerate_getadhoccredit($uid);
	$adhoc_balance = $adhoc_credit - $charge;

	// parent's when sender is a subuser
	if ($parent_uid = user_getparentbyuid($uid)) {
		$adhoc_credit_parent = simplerate_getadhoccredit($parent_uid);
		$adhoc_balance_parent = $adhoc_credit_parent - $charge;
	}

	if ($parent_uid) {
		if (($adhoc_balance_parent >= 0) && ($adhoc_balance >= 0)) {

			// update adhoc_credit immediately, parent's too
			simplerate_setadhoccredit($uid, $adhoc_balance);
			simplerate_setadhoccredit($parent_uid, $adhoc_balance_parent);

			_log("allowed subuser uid:" . $uid . " parent_uid:" . $parent_uid . " sms_to:" . $sms_to . " adhoc_credit:" . $adhoc_credit . " count:" . $count . " rate:" . $rate . " charge:" . $charge . " adhoc_balance:" . $adhoc_balance . " adhoc_balance_parent:" . $adhoc_balance_parent, 2, "simplerate_hook_rate_cansend");

			return TRUE;
		} else {
			_log("disallowed subuser uid:" . $uid . " parent_uid:" . $parent_uid . " sms_to:" . $sms_to . " adhoc_credit:" . $adhoc_credit . " count:" . $count . " rate:" . $rate . " charge:" . $charge . " adhoc_balance:" . $adhoc_balance . " adhoc_balance_parent:" . $adhoc_balance_parent, 2, "simplerate_hook_rate_cansend");

			return FALSE;
		}
	} else {
		if ($adhoc_balance >= 0) {

			// update adhoc_credit immediately
			simplerate_setadhoccredit($uid, $adhoc_balance);

			_log("allowed user uid:" . $uid . " sms_to:" . $sms_to . " adhoc_credit:" . $adhoc_credit . " count:" . $count . " rate:" . $rate . " charge:" . $charge . " adhoc_balance:" . $adhoc_balance, 2, "simplerate_hook_rate_cansend");

			return TRUE;
		} else {
			_log("disallowed user uid:" . $uid . " sms_to:" . $sms_to . " adhoc_credit:" . $adhoc_credit . " count:" . $count . " rate:" . $rate . " charge:" . $charge . " adhoc_balance:" . $adhoc_balance, 2, "simplerate_hook_rate_cansend");

			return FALSE;
		}
	}
}
