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

function simplebilling_hook_billing_post($smslog_id, $rate, $count, $charge)
{
	$ok = false;
	$rate = (isset($rate) ? $rate : 0);
	$count = (isset($count) ? $count : 0);
	$charge = (isset($charge) ? $charge : 0);

	// get parent_uid and uid from smslog_id, and also save them in tblBilling
	$db_query = "SELECT parent_uid,uid FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE smslog_id=?";
	$db_result = dba_query($db_query, [$smslog_id]);
	$db_row = dba_fetch_array($db_result);
	$parent_uid = (int) $db_row['parent_uid'];
	$uid = (int) $db_row['uid'];

	_log("saving smslog_id:" . $smslog_id . " parent_uid:" . $parent_uid . " uid:" . $uid . " rate:" . $rate . " count:" . $count . " charge:" . $charge, 3, "simplebilling_hook_billing_post");
	if ($uid) {
		$db_query = "
			INSERT INTO " . _DB_PREF_ . "_tblBilling (parent_uid,uid,post_datetime,smslog_id,rate,count,charge,status) VALUES (?,?,'" . core_get_datetime() . "',?,?,?,?,'0')";
		$db_argv = [
			$parent_uid,
			$uid,
			$smslog_id,
			$rate,
			$count,
			$charge
		];
		if ($smslog_id && ($id = dba_insert_id($db_query, $db_argv))) {
			_log("saved smslog_id:" . $smslog_id . " id:" . $id, 3, "simplebilling_hook_billing_post");
			$ok = true;
		} else {
			_log("fail to save unable to insert to db smslog_id:" . $smslog_id, 2, "simplebilling_hook_billing_post");
		}
	} else {
		_log("fail to save user not found smslog_id:" . $smslog_id . " parent_uid:" . $parent_uid . " uid:" . $uid, 2, "simplebilling_hook_billing_post");
	}

	return $ok;
}

function simplebilling_hook_billing_rollback($smslog_id)
{
	$ok = false;
	_log("checking smslog_id:" . $smslog_id, 2, "simplebilling rollback");
	$db_query = "SELECT id FROM " . _DB_PREF_ . "_tblBilling WHERE smslog_id=?";
	$db_result = dba_query($db_query, [$smslog_id]);
	if ($smslog_id && ($db_row = dba_fetch_array($db_result))) {
		$id = $db_row['id'];
		_log("saving smslog_id:" . $smslog_id . " id:" . $id, 3, "simplebilling rollback");
		$db_query = "UPDATE " . _DB_PREF_ . "_tblBilling SET status=2 WHERE id=?";
		if ($db_result = dba_affected_rows($db_query, [$id])) {
			_log("saved smslog_id:" . $smslog_id, 3, "simplebilling rollback");
			$ok = true;
		} else {
			_log("fail to save smslog_id:" . $smslog_id, 2, "simplebilling rollback");
		}
	} else {
		_log("fail to check smslog_id:" . $smslog_id, 2, "simplebilling rollback");
	}
	return $ok;
}

function simplebilling_hook_billing_finalize($smslog_id)
{
	$ok = false;
	_log("saving smslog_id:" . $smslog_id, 2, "simplebilling_hook_billing_finalize");
	$db_query = "UPDATE " . _DB_PREF_ . "_tblBilling SET c_timestamp='" . time() . "', status=1 WHERE smslog_id=?";
	if ($db_result = dba_affected_rows($db_query, [$smslog_id])) {
		_log("saved smslog_id:" . $smslog_id, 3, "simplebilling_hook_billing_finalize");
		$ok = true;
	} else {
		_log("fail to save smslog_id:" . $smslog_id, 3, "simplebilling_hook_billing_finalize");
	}
	return $ok;
}

function simplebilling_hook_dlr_update($smslog_id, $uid, $p_status)
{
	//_log("checking smslog_id:".$smslog_id, 2, "simplebilling_hook_dlr_update");
	if (($p_status == 1) || ($p_status == 3)) {
		$db_query = "SELECT status FROM " . _DB_PREF_ . "_tblBilling WHERE smslog_id=?";
		$db_result = dba_query($db_query, [$smslog_id]);
		if ($db_row = dba_fetch_array($db_result)) {
			if ((int) $db_row['status'] > 0) {
				// fixme anton - debug only
				//_log("billing finalized smslog_id:" . $smslog_id . " status:" . $db_row['status'], 3, "simplebilling_hook_dlr_update");
			} else {
				billing_finalize($smslog_id);
			}
		} else {
			_log("fail to find billing smslog_id:" . $smslog_id, 2, "simplebilling_hook_dlr_update");
		}
	}
}

function simplebilling_hook_billing_getdata($smslog_id)
{
	$ret = array();
	//_log("smslog_id:".$smslog_id, 2, "simplebilling getdata");
	$db_query = "SELECT id,post_datetime,rate,credit,count,charge,status FROM " . _DB_PREF_ . "_tblBilling WHERE smslog_id=?";
	$db_result = dba_query($db_query, [$smslog_id]);
	if ($db_row = dba_fetch_array($db_result)) {
		$id = $db_row['id'];
		$post_datetime = $db_row['post_datetime'];
		$rate = (float) $db_row['rate'];
		$credit = (float) $db_row['credit'];
		$count = (int) $db_row['count'];
		$charge = (float) $db_row['charge'];
		$status = $db_row['status'];
		$ret = array(
			'id' => $id,
			'smslog_id' => $smslog_id,
			'post_datetime' => $post_datetime,
			'status' => $status,
			'rate' => $rate,
			'credit' => $credit,
			'count' => $count,
			'charge' => $charge
		);
	}
	return $ret;
}

function simplebilling_hook_billing_getdata_by_uid($uid)
{
	$ret = array();
	// _log("uid:".$uid, 2, "simplebilling summary");
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblBilling WHERE status=1 AND uid=?";
	$db_result = dba_query($db_query, [$uid]);
	while ($db_row = dba_fetch_array($db_result)) {
		$id = $db_row['id'];
		$smslog_id = $db_row['smslog_id'];
		$post_datetime = $db_row['post_datetime'];
		$rate = $db_row['rate'];
		$credit = $db_row['credit'];
		$count = $db_row['count'];
		$charge = $db_row['charge'];
		$ret[] = array(
			'id' => $id,
			'smslog_id' => $smslog_id,
			'post_datetime' => $post_datetime,
			'rate' => $rate,
			'credit' => $credit,
			'count' => $count,
			'charge' => $charge
		);
	}
	return $ret;
}