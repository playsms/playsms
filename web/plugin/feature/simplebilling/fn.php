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

function simplebilling_hook_billing_post($smslog_id, $rate, $count, $charge, $uid, $parent_uid)
{
	$ok = false;

	$rate = isset($rate) ? (float) $rate : 0;
	$count = isset($count) ? (int) $count : 0;
	$charge = isset($charge) ? (float) $charge : 0;

	//_log("saving parent_uid:" . $parent_uid . " uid:" . $uid . " smslog_id:" . $smslog_id . " rate:" . $rate . " count:" . $count . " charge:" . $charge, 3, "simplebilling_hook_billing_post");
	$db_query = "INSERT INTO " . _DB_PREF_ . "_tblBilling (parent_uid,uid,post_datetime,smslog_id,rate,count,charge,status) VALUES (?,?,?,?,?,?,?,?)";
	if ($smslog_id && ($id = dba_insert_id($db_query, [$parent_uid, $uid, core_get_datetime(), $smslog_id, $rate, $count, $charge, 0]))) {
		_log("saved smslog_id:" . $smslog_id . " id:" . $id, 3, "simplebilling_hook_billing_post");
		$ok = true;
	} else {
		_log("fail to save smslog_id:" . $smslog_id, 3, "simplebilling_hook_billing_post");
	}

	return $ok;
}

function simplebilling_hook_billing_rollback($smslog_id)
{
	$ok = false;

	//_log("checking smslog_id:" . $smslog_id, 3, "simplebilling rollback");
	$db_query = "SELECT id FROM " . _DB_PREF_ . "_tblBilling WHERE smslog_id=?";
	$db_result = dba_query($db_query, [$smslog_id]);
	if ($smslog_id && ($db_row = dba_fetch_array($db_result))) {
		$id = $db_row['id'];
		_log("saving smslog_id:" . $smslog_id . " id:" . $id, 2, "simplebilling rollback");
		$db_query = "UPDATE " . _DB_PREF_ . "_tblBilling SET status='2' WHERE id=?";
		if (dba_affected_rows($db_query, [$id])) {
			_log("saved smslog_id:" . $smslog_id, 3, "simplebilling rollback");
			$ok = true;
		} else {
			_log("fail to save smslog_id:" . $smslog_id, 3, "simplebilling rollback");
		}
	} else {
		_log("fail to check smslog_id:" . $smslog_id, 3, "simplebilling rollback");
	}

	return $ok;
}

function simplebilling_hook_billing_finalize($smslog_id)
{
	$ok = false;

	//_log("saving smslog_id:" . $smslog_id, 2, "simplebilling finalize");
	$db_query = "UPDATE " . _DB_PREF_ . "_tblBilling SET status='1' WHERE smslog_id=?";
	if (dba_affected_rows($db_query, [$smslog_id])) {
		//_log("saved smslog_id:" . $smslog_id, 3, "simplebilling finalize");
		$ok = true;
	} else {
		_log("fail to save smslog_id:" . $smslog_id, 3, "simplebilling finalize");
	}

	return $ok;
}

function simplebilling_hook_setsmsdeliverystatus($smslog_id, $uid, $p_status)
{
	//_log("checking smslog_id:".$smslog_id, 2, "simplebilling setsmsdeliverystatus");
	$db_query = "SELECT id FROM " . _DB_PREF_ . "_tblBilling WHERE status=0 AND smslog_id=? LIMIT 1";
	$db_result = dba_query($db_query, [$smslog_id]);
	if (dba_fetch_array($db_result)) {
		if (($p_status == 1) || ($p_status == 3)) {
			simplebilling_hook_billing_finalize($smslog_id);
		} else if ($p_status == 2) {
			simplebilling_hook_billing_refund($smslog_id);
		}
	}
}

function simplebilling_hook_billing_getdata($smslog_id)
{
	$ret = [];

	//_log("smslog_id:".$smslog_id, 2, "simplebilling getdata");
	$db_query = "SELECT id,post_datetime,rate,credit,count,charge,status FROM " . _DB_PREF_ . "_tblBilling WHERE smslog_id=?";
	$db_result = dba_query($db_query, [$smslog_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$id = (int) $db_row['id'];
		$post_datetime = core_sanitize_string($db_row['post_datetime']);
		$rate = (float) $db_row['rate'];
		$credit = (float) $db_row['credit'];
		$count = (int) $db_row['count'];
		$charge = (float) $db_row['charge'];
		$status = (int) $db_row['status'];
		$ret = [
			'id' => $id,
			'smslog_id' => $smslog_id,
			'post_datetime' => $post_datetime,
			'status' => $status,
			'rate' => $rate,
			'credit' => $credit,
			'count' => $count,
			'charge' => $charge,
		];
	}

	return $ret;
}

function simplebilling_hook_billing_getdata_by_uid($uid)
{
	$ret = [];

	// _log("uid:".$uid, 2, "simplebilling summary");
	$db_query = "SELECT id,smslog_id,post_datetime,rate,credit,count,charge FROM " . _DB_PREF_ . "_tblBilling WHERE uid=?";
	$db_result = dba_query($db_query, [$uid]);
	while ($db_row = dba_fetch_array($db_result)) {
		$id = (int) $db_row['id'];
		$smslog_id = (int) $db_row['smslog_id'];
		$post_datetime = core_sanitize_string($db_row['post_datetime']);
		$rate = (float) $db_row['rate'];
		$credit = (float) $db_row['credit'];
		$count = (int) $db_row['count'];
		$charge = (float) $db_row['charge'];
		$ret[] = [
			'id' => $id,
			'smslog_id' => $smslog_id,
			'post_datetime' => $post_datetime,
			'rate' => $rate,
			'credit' => $credit,
			'count' => $count,
			'charge' => $charge,
		];
	}

	return $ret;
}

function simplebilling_hook_billing_deduct($smslog_id)
{
	global $core_config;

	_log("enter smslog_id:" . $smslog_id, 2, "simplebilling_hook_billing_deduct");
	$db_query = "SELECT p_dst,p_footer,p_msg,uid,parent_uid,unicode FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE smslog_id=?";
	$db_result = dba_query($db_query, [$smslog_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$p_dst = $db_row['p_dst'];
		$p_msg = $db_row['p_msg'];
		$p_footer = $db_row['p_footer'];
		$uid = (int) $db_row['uid'];
		$parent_uid = (int) $db_row['parent_uid'];
		$unicode = (int) $db_row['unicode'];
		if ($p_dst && $p_msg && $uid) {

			// get charge
			list($count, $rate, $charge) = rate_getcharges($uid, core_smslen($p_msg . $p_footer), $unicode, $p_dst);

			if (simplebilling_hook_billing_post($smslog_id, $rate, $count, $charge, $uid, $parent_uid)) {
				_log("deduct successful uid:" . $uid . " parent_uid:" . $parent_uid . " smslog_id:" . $smslog_id, 3, "simplebilling_hook_billing_deduct");

				return true;
			} else {
				_log("deduct failed uid:" . $uid . " parent_uid:" . $parent_uid . " smslog_id:" . $smslog_id, 3, "simplebilling_hook_billing_deduct");

				return false;
			}
		} else {
			_log("rate deduct failed due to empty data uid:" . $uid . " parent_uid:" . $parent_uid . " smslog_id:" . $smslog_id, 3, "simplebilling_hook_billing_deduct");
		}
	} else {
		_log("rate deduct failed due to missing data smslog_id:" . $smslog_id, 3, "simplebilling_hook_billing_deduct");
	}

	return false;
}

function simplebilling_hook_billing_refund($smslog_id)
{
	global $core_config;

	_log("start smslog_id:" . $smslog_id, 2, "simplebilling_hook_billing_refund");
	$db_query = "SELECT p_dst,p_msg,uid FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE p_status='2' AND smslog_id=?";
	$db_result = dba_query($db_query, [$smslog_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$p_dst = $db_row['p_dst'];
		$p_msg = $db_row['p_msg'];
		$uid = (int) $db_row['uid'];
		if ($p_dst && $p_msg && $uid) {
			if (simplebilling_hook_billing_rollback($smslog_id)) {

				return true;
			}
		}
	}

	return false;
}
