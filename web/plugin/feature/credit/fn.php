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

/**
 * Get user credit balance
 *
 * @param integer $uid
 *        User ID
 * @return float User credit balance
 */
function credit_getbalance($uid) {
	$balance = 0;
	
	if ($username = user_uid2username($uid)) {
		$balance = rate_getusercredit($username);
	}
	
	$balance = (float) $balance;
	$balance = number_format($balance, 2, '.', '');
	
	return $balance;
}

/**
 * Add credit to user
 *
 * @param integer $uid
 *        User ID
 * @param decimal $amount
 *        Credit amount to add (positive value)
 * @return boolean TRUE on success
 */
function credit_add($uid, $amount) {
	$amount = (float) $amount;
	return rate_addusercredit($uid, $amount);
}

/**
 * Reduce credit from user
 *
 * @param integer $uid
 *        User ID
 * @param decimal $amount
 *        Credit amount to reduce (positive value)
 * @return boolean TRUE on success
 */
function credit_reduce($uid, $amount) {
	$amount = (float) $amount;
	return rate_deductusercredit($uid, $amount);
}

/**
 * Get HTML component select all users
 *
 * @return string HTML component select
 */
function credit_html_select_user() {
	global $user_config;
	
	if (auth_isadmin()) {
		$admins = user_getallwithstatus(2);
		$users = user_getallwithstatus(3);
	}
	$subusers = user_getsubuserbyuid($user_config['uid']);
	
	if (count($admins) > 0) {
		$option_user .= '<optgroup label="' . _('Administrators') . '">';
		
		foreach ($admins as $admin) {
			$option_user .= '<option value="' . $admin['uid'] . '">' . $admin['name'] . ' (' . $admin['username'] . ') - ' . _('Administrator') . '</option>';
		}
		$option_user .= '</optgroup>';
	}
	
	if (count($users) > 0) {
		$option_user .= '<optgroup label="' . _('Users') . '">';
		
		foreach ($users as $user) {
			$option_user .= '<option value="' . $user['uid'] . '">' . $user['name'] . ' (' . $user['username'] . ') - ' . _('User') . '</option>';
		}
		$option_user .= '</optgroup>';
	}
	
	if (count($subusers) > 0) {
		$option_user .= '<optgroup label="' . _('Subusers') . '">';
		
		foreach ($subusers as $subuser) {
			$option_user .= '<option value="' . $subuser['uid'] . '">' . $subuser['name'] . ' (' . $subuser['username'] . ') - ' . _('Subuser') . '</option>';
		}
		$option_user .= '</optgroup>';
	}
	
	$select_user = '<select multiple name="uids[]" id="playsms-credit-select-user">' . $option_user . '</select>';
	
	return $select_user;
}

function credit_hook_webservices_output($operation, $requests, $returns) {
	global $user_config;
	
	if ($operation != 'credit') {
		return FALSE;
	}
	
	$balance = (float) 0;
	if (auth_isvalid()) {
		$balance = (float) credit_getbalance($user_config['uid']);
	}
	$balance = number_format($balance, 2, '.', '');
	
	$returns['modified'] = TRUE;
	$returns['param']['content'] = $balance;
	$returns['param']['content-type'] = 'text/plain';
	
	return $returns;
}

function credit_hook_rate_addusercredit($uid, $amount) {
	global $plugin_config;
	
	$db_table = $plugin_config['credit']['db_table'];
	$parent_uid = user_getparentbyuid($uid);
	$username = user_uid2username($uid);
	$status = user_getfieldbyuid($uid, 'status');
	$amount = (float) $amount;
	
	if (abs($amount) <= 0) {
		_log('amount cannot be zero. amount:[' . $amount . ']', 2, 'credit_hook_rate_addusercredit');
		
		return FALSE;
	}
	
	// record it
	$id = dba_add($db_table, array(
		'parent_uid' => $parent_uid,
		'uid' => $uid,
		'username' => $username,
		'status' => $status,
		'create_datetime' => core_get_datetime(),
		'amount' => $amount,
		'flag_deleted' => 0 
	));
	
	// update user's credit
	if ($id) {
		// set never been notified
		registry_update($uid, 'feature', 'credit', array(
			'lowest_limit_notif' => FALSE 
		));
		
		_log('saved id:' . $id . ' parent_uid:' . $parent_uid . ' uid:' . $uid . ' username:' . $username . ' amount:' . $amount . ' balance:' . $balance, 3, 'credit_add');
		
		return TRUE;
	} else {
		_log('fail to save parent_uid:' . $parent_uid . ' uid:' . $uid . ' username:' . $username . ' amount:' . $amount . ' balance:' . $balance, 3, 'credit_add');
		
		return FALSE;
	}
}

function credit_hook_rate_deductusercredit($uid, $amount) {
	
	// the amount is always negative to reduce balance
	$amount = -1 * abs($amount);
	
	return credit_add($uid, $amount);
}

function credit_hook_rate_getusercredit($username) {
	$balance = 0;
	
	if ($username) {
		$db_query = "SELECT credit FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND username='$username'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$balance = $db_row['credit'];
	}
	$balance = (float) ($balance ? $balance : 0);
	$balance = number_format($balance, 2, '.', '');
	
	return $balance;
}

function _credit_get_credit($uid) {
	$credit = 0;
	
	if ($c_uid = (int) $uid) {
		$db_query = "SELECT SUM(amount) AS credit FROM " . _DB_PREF_ . "_featureCredit WHERE uid='$c_uid' AND flag_deleted='0'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$credit = $db_row['credit'];
	}
	
	return $credit;
}

function _credit_get_billing($uid) {
	$billing = 0;
	
	if ($c_uid = (int) $uid) {
		$db_query = "SELECT SUM(A.charge) AS billing FROM " . _DB_PREF_ . "_tblBilling A LEFT JOIN " . _DB_PREF_ . "_tblSMSOutgoing B ON A.smslog_id=B.smslog_id AND A.status='1' AND B.uid='$c_uid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$billing = $db_row['billing'];
	}
	
	return $billing;
}

function _credit_calculate_balance($credit, $billing) {
	$balance = (float) $credit - (float) $billing;
	$balance = (float) ($balance ? $balance : 0);
	$balance = number_format($balance, 2, '.', '');
	
	return $balance;
}

function _credit_update_user($uid, $balance) {
	$balance = (float) $balance;
	
	if ($c_uid = (int) $uid) {
		$db_query = "UPDATE " . _DB_PREF_ . "_tblUser SET credit='$balance' WHERE uid='$c_uid' AND flag_deleted='0'";
		dba_query($db_query);
	}
}

function _credit_rate_update($uid, $status) {
	if (($c_uid = (int) $uid) && ($c_status = (int) $status)) {
		// get credit
		$credit = _credit_get_credit($c_uid);
		
		// get billing
		$billing = _credit_get_billing($c_uid);
		
		// get billing for subusers if this is admin or user
		if (($c_status == 2) || ($c_status == 3)) {
			$subusers = user_getsubuserbyuid($c_uid);
			foreach ($subusers as $subuser) {
				$billing += _credit_get_billing($subuser['uid']);
			}
		}
		
		// calculate balance
		$balance = _credit_calculate_balance($credit, $billing);
		
		// update user's credit field with balance
		_credit_update_user($c_uid, $balance);
	}
}

function credit_hook_rate_update($username) {
	if (!core_playsmsd_timer(30)) {
		return;
	}
	
	if (trim($username) && ($c_uid = user_username2uid($username))) {
		$c_status = user_getfieldbyuid($c_uid, 'status');
		_credit_rate_update($c_uid, $c_status);
	} else {
		$db_query = "SELECT uid, status FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0'";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$c_uid = $db_row['uid'];
			_credit_rate_update($c_uid);
		}
	}
}
