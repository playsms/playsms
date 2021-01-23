<?php
defined('_SECURE_') or die('Forbidden');

function simplebilling_hook_billing_post($smslog_id, $rate, $count, $charge) {
	$ok = false;
	$rate = ( isset($rate) ? $rate : 0 );
	$count = ( isset($count) ? $count : 0 );
	$charge = ( isset($charge) ? $charge : 0 );

	// get parent_uid and uid from smslog_id, and also save them in tblBilling
	$db_query = "SELECT parent_uid,uid FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$parent_uid = (int) $db_row['parent_uid'];
	$uid = (int) $db_row['uid'];

	_log("saving smslog_id:" . $smslog_id . " parent_uid:" . $parent_uid . " uid:" . $uid . " rate:" . $rate . " count:" . $count . " charge:" . $charge, 2, "simplebilling_hook_billing_post");
	if ($uid) {
		$db_query = "INSERT INTO " . _DB_PREF_ . "_tblBilling (parent_uid,uid,post_datetime,smslog_id,rate,count,charge,status) VALUES ('$parent_uid','$uid','" . core_get_datetime() . "','$smslog_id','$rate','$count','$charge','0')";
		if ($smslog_id && ($id = dba_insert_id($db_query))) {
			_log("saved smslog_id:" . $smslog_id . " id:" . $id, 2, "simplebilling_hook_billing_post");
			$ok = true;
		} else {
			_log("fail to save unable to insert to db smslog_id:" . $smslog_id, 2, "simplebilling_hook_billing_post");
		}
	} else {
		_log("fail to save user not found smslog_id:" . $smslog_id . " parent_uid:" . $parent_uid . " uid:" . $uid, 2, "simplebilling_hook_billing_post");
	}

	return $ok;
}

function simplebilling_hook_billing_rollback($smslog_id) {
	$ok = false;
	_log("checking smslog_id:" . $smslog_id, 2, "simplebilling rollback");
	$db_query = "SELECT id FROM " . _DB_PREF_ . "_tblBilling WHERE smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
	if ($smslog_id && ($db_row = dba_fetch_array($db_result))) {
		$id = $db_row['id'];
		_log("saving smslog_id:" . $smslog_id . " id:" . $id, 2, "simplebilling rollback");
		$db_query = "UPDATE " . _DB_PREF_ . "_tblBilling SET status='2' WHERE id='$id'";
		if ($db_result = dba_affected_rows($db_query)) {
			_log("saved smslog_id:" . $smslog_id, 2, "simplebilling rollback");
			$ok = true;
		} else {
			_log("fail to save smslog_id:" . $smslog_id, 2, "simplebilling rollback");
		}
	} else {
		_log("fail to check smslog_id:" . $smslog_id, 2, "simplebilling rollback");
	}
	return $ok;
}

function simplebilling_hook_billing_finalize($smslog_id) {
	$ok = false;
	_log("saving smslog_id:" . $smslog_id, 2, "simplebilling_hook_billing_finalize");
	$db_query = "UPDATE " . _DB_PREF_ . "_tblBilling SET c_timestamp='" . time() . "', status='1' WHERE smslog_id='$smslog_id'";
	if ($db_result = dba_affected_rows($db_query)) {
		_log("saved smslog_id:" . $smslog_id, 2, "simplebilling_hook_billing_finalize");
		$ok = true;
	} else {
		_log("fail to save smslog_id:" . $smslog_id, 2, "simplebilling_hook_billing_finalize");
	}
	return $ok;
}

function simplebilling_hook_setsmsdeliverystatus($smslog_id, $uid, $p_status) {
	//_log("checking smslog_id:".$smslog_id, 2, "simplebilling_hook_setsmsdeliverystatus");
	if (($p_status == 1) || ($p_status == 3)) {
		$db_query = "SELECT status FROM " . _DB_PREF_ . "_tblBilling WHERE smslog_id='$smslog_id'";
		$db_result = dba_query($db_query);
		if ($db_row = dba_fetch_array($db_result)) {
			if ((int) $db_row['status'] > 0) {
				_log("billing finalized smslog_id:" . $smslog_id . " status:" . $db_row['status'], 2, "simplebilling_hook_setsmsdeliverystatus");
			} else {
				billing_finalize($smslog_id);
			} 
		} else {
			_log("fail to find billing smslog_id:" . $smslog_id, 2, "simplebilling_hook_setsmsdeliverystatus");
		}
	}
}

function simplebilling_hook_billing_getdata($smslog_id) {
	$ret = array();
	//_log("smslog_id:".$smslog_id, 2, "simplebilling getdata");
	$db_query = "SELECT id,post_datetime,rate,credit,count,charge,status FROM " . _DB_PREF_ . "_tblBilling WHERE smslog_id='$smslog_id'";
	$db_result = dba_query($db_query);
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

function simplebilling_hook_billing_getdata_by_uid($uid) {
	$ret = array();
	// _log("uid:".$uid, 2, "simplebilling summary");
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblBilling WHERE status='1' AND uid='$uid'";
	$db_result = dba_query($db_query);
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
