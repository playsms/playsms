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
	if ($id) {
		$db_query = "SELECT dst FROM " . _DB_PREF_ . "_featureSimplerate WHERE id=?";
		$db_result = dba_query($db_query, [$id]);
		$db_row = dba_fetch_array($db_result);
		$dst = $db_row['dst'];
	}
	return $dst;
}

function simplerate_getprefix($id)
{
	if ($id) {
		$db_query = "SELECT prefix FROM " . _DB_PREF_ . "_featureSimplerate WHERE id=?";
		$db_result = dba_query($db_query, [$id]);
		$db_row = dba_fetch_array($db_result);
		$prefix = $db_row['prefix'];
	}
	return $prefix;
}

function simplerate_getbyid($id)
{
	if ($id) {
		$db_query = "SELECT rate FROM " . _DB_PREF_ . "_featureSimplerate WHERE id=?";
		$db_result = dba_query($db_query, [$id]);
		$db_row = dba_fetch_array($db_result);
		$rate = $db_row['rate'];
	}
	$rate = (($rate > 0) ? $rate : 0);
	return $rate;
}

function simplerate_getcard($id)
{
	$card = [];

	if ($id = (int) $id) {
		if (
			$list = dba_search(_DB_PREF_ . '_featureSimplerate_card', '*', [
				'id' => $id,
			])
		) {
			$card = $list[0];
		}
	}

	return $card;
}

function simplerate_getcardsbyuid($uid)
{
	$cards = [];

	if ($uid = (int) $uid) {

		// SELECT C.id, C.name, C.notes, C.created, C.last_update FROM `playsms_featureSimplerate_card` C 
		// LEFT JOIN `playsms_featureSimplerate_card_user` CU ON C.id = CU.card_id 
		// LEFT JOIN `playsms_tblUser` U ON CU.uid = U.uid WHERE U.uid = '1'

		if (
			$list = dba_search(_DB_PREF_ . "_featureSimplerate_card C", 'C.id, C.name, C.notes, C.created, C.last_update', [
				'U.uid' => $uid,
			], '', '', "
			LEFT JOIN " . _DB_PREF_ . "_featureSimplerate_card_user CU ON C.id=CU.card_id
			LEFT JOIN " . _DB_PREF_ . "_tblUser U ON CU.uid=U.uid
		")
		) {
			$cards = $list;
		}
	}

	return $cards;
}

// -----------------------------------------------------------------------------------------
function simplerate_getadhoccredit($uid)
{
	$balance = 0;

	if ($c_uid = (int) $uid) {
		$db_query = "SELECT adhoc_credit FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted=0 AND uid=?";
		$db_result = dba_query($db_query, [$c_uid]);
		$db_row = dba_fetch_array($db_result);
		$balance = (float) $db_row['adhoc_credit'];
	}

	return $balance;
}

function simplerate_setadhoccredit($uid, $balance)
{
	$balance = (float) $balance;

	if ($c_uid = (int) $uid) {
		$db_query = "UPDATE " . _DB_PREF_ . "_tblUser SET c_timestamp='" . time() . "', adhoc_credit=? WHERE uid=? AND flag_deleted=0";
		if (dba_affected_rows($db_query, [$balance, $c_uid]) === 0) {
			_log("Fail to update data u:" . $c_uid . " balance:" . $balance, 3, "simplerate_setadhoccredit");
		}
	}
}

// -----------------------------------------------------------------------------------------
function simplerate_hook_rate_getbyuid($uid, $sms_to = '')
{
	$rates = [];

	$cards = simplerate_getcardsbyuid($uid);

	foreach ( $cards as $card ) {
		if (isset($card['id']) && (int) $card['id']) {

			// SELECT prefix, rate, dst FROM `playsms_featureSimplerate` R
			// LEFT JOIN `playsms_featureSimplerate_card_rate` CR ON R.id=CR.rate_id
			// WHERE CR.card_id='1'
			// ORDER BY prefix DESC

			if (
				$list = dba_search(_DB_PREF_ . "_featureSimplerate R", 'R.id AS rate_id, prefix, rate, dst, card_id', [
					'CR.card_id' => $card['id'],
				], '', '', "LEFT JOIN " . _DB_PREF_ . "_featureSimplerate_card_rate CR ON R.id=CR.rate_id")
			) {
				$rates = array_merge($rates, $list);
			}
		}
	}

	// sort by prefix
	$col = array_column($rates, 'prefix');
	array_multisort($col, SORT_DESC, $rates);

	// eliminate double prefix, choose the 1st match
	$r = [];
	foreach ( $rates as $rate ) {
		if (!isset($r[$rate['prefix']])) {
			$r[$rate['prefix']] = [
				'rate_id' => $rate['rate_id'],
				'rate' => $rate['rate'],
				'dst' => $rate['dst'],
				'card_id' => $rate['card_id'],
			];
		}
	}

	// final format
	$rates = [];
	foreach ( $r as $key => $val ) {
		if (isset($key) && isset($val['card_id'])) {
			$rates[] = [
				'prefix' => $key,
				'rate_id' => $val['rate_id'],
				'rate' => $val['rate'],
				'dst' => $val['dst'],
				'card_id' => $val['card_id'],
			];
		}
	}

	return $rates;
}

function simplerate_hook_rate_getbyprefix($sms_to, $uid = '')
{
	global $core_config;
	$found = FALSE;

	$default_rate = (float) ($core_config['main']['default_rate'] > 0 ? $core_config['main']['default_rate'] : 0);
	$rate = $default_rate;
	$to = core_sanitize_numeric($sms_to);

	$effective_row = [];
	$list = rate_getbyuid($uid);
	foreach ( $list as $row ) {
		if ($found) {
			break;
		}
		$m = (strlen($$to) > 10 ? 10 : strlen($to));
		for ($i = $m + 1; $i > 0; $i--) {
			$prefix = substr($to, 0, $i);
			if ($prefix == $row['prefix']) {
				$rate = $row['rate'];
				$effective_row = $row;
				$found = TRUE;
				break;
			}
		}
	}
	$row = $effective_row;

	if ($found) {
		_log("found rate rate_id:" . $row['rate_id'] . " prefix:" . $row['prefix'] . " rate:" . $rate . " dst:" . $row['dst'] . " to:" . $sms_to . " uid:" . $uid, 3, "simplerate_hook_rate_getbyprefix");
	} else {
		_log("rate not found to:" . $sms_to . " default_rate:" . $default_rate . " uid:" . $uid, 3, "simplerate_hook_rate_getbyprefix");
	}

	$rate = (float) (($rate > 0) ? $rate : 0);

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
	$rate = rate_getbyprefix($sms_to, $uid);
	$charge = $count * $rate;

	_log('uid:' . $uid . ' u:' . $user['username'] . ' len:' . $sms_len . ' unicode:' . $unicode . ' to:' . $sms_to . ' enable_credit_unicode:' . (int) $user['opt']['enable_credit_unicode'] . ' count:' . $count . ' rate:' . $rate . ' charge:' . $charge, 3, 'simplerate_hook_rate_getcharges');

	return array(
		$count,
		$rate,
		$charge
	);
}

function simplerate_hook_rate_cansend($uid, $sms_len, $unicode, $sms_to)
{
	global $core_config;

	list($count, $rate, $charge) = rate_getcharges($uid, $sms_len, $unicode, $sms_to);

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

function simplerate_hook_rate_deduct($smslog_id)
{
	global $core_config;

	_log("enter smslog_id:" . $smslog_id, 2, "simplerate_hook_rate_deduct");
	$db_query = "SELECT p_dst,p_footer,p_msg,uid,parent_uid,unicode FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE smslog_id=?";
	$db_result = dba_query($db_query, [$smslog_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$p_dst = $db_row['p_dst'];
		$p_msg = $db_row['p_msg'];
		$p_footer = $db_row['p_footer'];
		$uid = $db_row['uid'];
		$parent_uid = $db_row['parent_uid'];
		$unicode = $db_row['unicode'];
		if ($p_dst && $p_msg && $uid) {

			// get charge
			list($count, $rate, $charge) = rate_getcharges($uid, core_smslen($p_msg . $p_footer), $unicode, $p_dst);

			if (billing_post($smslog_id, $rate, $count, $charge)) {
				_log("deduct successful uid:" . $uid . " parent_uid:" . $parent_uid . " smslog_id:" . $smslog_id, 3, "simplerate_hook_rate_deduct");

				return TRUE;
			} else {
				_log("deduct failed uid:" . $uid . " parent_uid:" . $parent_uid . " smslog_id:" . $smslog_id, 3, "simplerate_hook_rate_deduct");

				return FALSE;
			}
		} else {
			_log("rate deduct failed due to empty data uid:" . $uid . " parent_uid:" . $parent_uid . " smslog_id:" . $smslog_id, 3, "simplerate_hook_rate_deduct");
		}
	} else {
		_log("rate deduct failed due to missing data smslog_id:" . $smslog_id, 3, "simplerate_hook_rate_deduct");
	}

	return FALSE;
}

function simplerate_hook_rate_refund($smslog_id)
{
	global $core_config;

	_log("start smslog_id:" . $smslog_id, 2, "simplerate_hook_rate_refund");
	$db_query = "SELECT p_dst,p_msg,uid FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE p_status=2 AND smslog_id=?";
	$db_result = dba_query($db_query, [$smslog_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$p_dst = $db_row['p_dst'];
		$p_msg = $db_row['p_msg'];
		$uid = $db_row['uid'];
		if ($p_dst && $p_msg && $uid) {
			if (billing_rollback($smslog_id)) {

				return TRUE;
			}
		}
	}

	return FALSE;
}

function simplerate_hook_dlr_update($smslog_id, $uid, $p_status)
{
	//_log("start smslog_id:".$smslog_id, 2, "simplerate_hook_dlr_update");
	if ($p_status == 2) {
		// check in billing table smslog_id with status=0, status=1 is finalized, status=2 is rolled-back
		$db_query = "SELECT id FROM " . _DB_PREF_ . "_tblBilling WHERE status=0 AND smslog_id=?";
		$db_result = dba_query($db_query, [$smslog_id]);
		if ($db_row = dba_fetch_array($db_result)) {
			rate_refund($smslog_id);
		}
	}
}