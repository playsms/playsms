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
 * Get user credit credit
 *
 * @param int $uid User ID
 * @return float User credit credit
 */
function credit_getcredit($uid)
{
	$credit = 0;

	if ($username = user_uid2username($uid)) {
		$credit = rate_getusercredit($username);
	}

	$credit = (float) $credit;

	return $credit;
}

/**
 * Add credit to user
 *
 * @param int $uid User ID
 * @param float|int $amount Credit amount to add (positive value)
 * @return bool true on success
 */
function credit_add($uid, $amount)
{
	$amount = (float) $amount;

	return rate_addusercredit($uid, $amount);
}

/**
 * Reduce credit from user
 *
 * @param int $uid User ID
 * @param float|int $amount Credit amount to reduce (positive value)
 * @return bool true on success
 */
function credit_reduce($uid, $amount)
{
	$amount = (float) $amount;

	return rate_deductusercredit($uid, $amount);
}

/**
 * Get HTML component select all users
 *
 * @return string HTML component select
 */
function credit_html_select_user()
{
	global $user_config;

	if (auth_isadmin()) {
		$admins = user_getallwithstatus(2);
		$users = user_getallwithstatus(3);
	} else {
		$admins = [];
		$users = [];
	}
	$subusers = user_getsubuserbyuid($user_config['uid']);

	$option_user = '';

	if (count($admins) > 0) {
		$option_user .= '<optgroup label="' . _('Administrators') . '">';

		foreach ( $admins as $admin ) {
			$option_user .= '<option value="' . $admin['uid'] . '">' . $admin['name'] . ' (' . $admin['username'] . ') - ' . _('Administrator') . '</option>';
		}
		$option_user .= '</optgroup>';
	}

	if (count($users) > 0) {
		$option_user .= '<optgroup label="' . _('Users') . '">';

		foreach ( $users as $user ) {
			$option_user .= '<option value="' . $user['uid'] . '">' . $user['name'] . ' (' . $user['username'] . ') - ' . _('User') . '</option>';
		}
		$option_user .= '</optgroup>';
	}

	if (count($subusers) > 0) {
		$option_user .= '<optgroup label="' . _('Subusers') . '">';

		foreach ( $subusers as $subuser ) {
			$option_user .= '<option value="' . $subuser['uid'] . '">' . $subuser['name'] . ' (' . $subuser['username'] . ') - ' . _('Subuser') . '</option>';
		}
		$option_user .= '</optgroup>';
	}

	$select_user = '<select multiple name="uids[]" id="playsms-credit-select-user">' . $option_user . '</select>';

	return $select_user;
}

/**
 * Get user credit via webservices
 * This function hooks webservices_output()
 * 
 * @param string $operation webservices operation keyword
 * @param mixed $requests request data
 * @param mixed $returns default or previous returns
 * @return array|bool
 */
function credit_hook_webservices_output($operation, $requests, $returns)
{
	global $user_config;

	if (strtolower(trim($operation)) != 'credit') {
		return false;
	}

	$credit = (float) 0;
	if (auth_isvalid()) {
		$credit = (float) credit_getcredit($user_config['uid']);
	}

	$returns['modified'] = true;
	$returns['param']['content'] = core_display_credit($credit);
	$returns['param']['content-type'] = 'text/plain';

	return $returns;
}

/**
 * Add user credit
 * This function hooks rate_addusercredit()
 * 
 * @param int $uid
 * @param float|int $amount
 * @return bool
 */
function credit_hook_rate_addusercredit($uid, $amount)
{
	global $plugin_config;

	$db_table = $plugin_config['credit']['db_table'] ? $plugin_config['credit']['db_table'] : _DB_PREF_ . '_featureCredit';
	$amount = (float) $amount;

	$username = user_uid2username($uid);
	$status = user_getfieldbyuid($uid, 'status');
	$parent_uid = (int) ($status == 4 ? user_getparentbyuid($uid) : 0);

	if (abs($amount) <= 0) {
		_log('amount cannot be zero. amount:[' . $amount . ']', 2, 'credit_hook_rate_addusercredit');

		return false;
	}

	// record it
	$id = dba_add(
		$db_table,
		[
			'parent_uid' => $parent_uid,
			'uid' => $uid,
			'username' => $username,
			'status' => $status,
			'create_datetime' => core_get_datetime(),
			'amount' => $amount,
			'flag_deleted' => 0
		]
	);

	// update user's credit
	if ($id) {
		// set never been notified
		registry_update(
			$uid,
			'feature',
			'credit',
			[
				'lowest_limit_notif' => false
			]
		);

		_log('saved id:' . $id . ' parent_uid:' . $parent_uid . ' uid:' . $uid . ' username:' . $username . ' amount:' . $amount, 3, 'credit_add');

		return true;
	} else {
		_log('fail to save parent_uid:' . $parent_uid . ' uid:' . $uid . ' username:' . $username . ' amount:' . $amount, 3, 'credit_add');

		return false;
	}
}

/**
 * Deduct user credit
 * This function hooks rate_deductusercredit()
 * 
 * @param int $uid
 * @param float|int $amount
 * @return bool
 */
function credit_hook_rate_deductusercredit($uid, $amount)
{
	// the amount is always negative to reduce credit
	$amount = -1 * abs($amount);

	return credit_add($uid, $amount);
}

/**
 * Get user credit
 * This function hooks rate_addusercredit()
 * 
 * @param string $username
 * @return float
 */
function credit_hook_rate_getusercredit($username)
{
	$credit = 0;

	if ($username = trim($username)) {
		$db_query = "SELECT credit FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0' AND username=?";
		$db_result = dba_query($db_query, [$username]);
		$db_row = dba_fetch_array($db_result);
		$credit = $db_row['credit'];
	}

	return (float) $credit;
}

/**
 * Get user credit by uid
 * 
 * @param int $uid
 * @return float
 */
function _credit_get_credit($uid)
{
	$credit = 0;

	if ($uid = (int) $uid) {
		$db_query = "SELECT SUM(amount) AS credit FROM " . _DB_PREF_ . "_featureCredit WHERE uid=? AND flag_deleted='0'";
		$db_result = dba_query($db_query, [$uid]);
		$db_row = dba_fetch_array($db_result);
		$credit = $db_row['credit'];
	}

	return (float) $credit;
}

/**
 * Get user's current billing by uid
 * 
 * @param int $uid
 * @return float
 */
function _credit_get_billing($uid)
{
	$billing = 0;

	if ($uid = (int) $uid) {
		$db_query = "SELECT SUM(A.charge) AS billing FROM " . _DB_PREF_ . "_tblBilling A INNER JOIN " . _DB_PREF_ . "_tblSMSOutgoing B ON A.smslog_id=B.smslog_id AND A.status='1' AND B.uid=?";
		$db_result = dba_query($db_query, [$uid]);
		$db_row = dba_fetch_array($db_result);
		$billing = $db_row['billing'];
	}

	return (float) $billing;
}

/**
 * Get user's parent current billing by parent's uid
 * 
 * @param int $uid
 * @return float
 */
function _credit_get_billing_parent($parent_uid)
{
	$billing = 0;

	if ($parent_uid = (int) $parent_uid) {
		$db_query = "SELECT SUM(A.charge) AS billing FROM " . _DB_PREF_ . "_tblBilling A INNER JOIN " . _DB_PREF_ . "_tblSMSOutgoing B ON A.smslog_id=B.smslog_id AND A.status='1' AND B.parent_uid=?";
		$db_result = dba_query($db_query, [$parent_uid]);
		$db_row = dba_fetch_array($db_result);
		$billing = $db_row['billing'];
	}

	return (float) $billing;
}

/**
 * Calculate credit due to billing
 * 
 * @param float $credit
 * @param float $billing
 * @return float
 */
function _credit_calculate_credit($credit, $billing)
{
	$credit = (float) $credit;
	$billing = (float) $billing;

	$credit = $credit - $billing;

	return $credit;
}

/**
 * Update user credit and set adhoc credit
 * 
 * @param int $uid
 * @param float $credit
 * @return void
 */
function _credit_update_credit($uid, $credit)
{
	$credit = (float) $credit;

	if ($uid = (int) $uid) {
		$db_query = "UPDATE " . _DB_PREF_ . "_tblUser SET c_timestamp=?, credit=?, adhoc_credit=? WHERE uid=? AND flag_deleted='0'";
		dba_query($db_query, [
			time(),
			$credit,
			$credit,
			$uid,
		]);
	}
}

/**
 * Process all user's credit update every 30 seconds
 * 
 * @return void
 */
function credit_hook_rate_update()
{
	if (!core_playsmsd_timer(30)) {
		return;
	}

	$db_query = "SELECT uid, status FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		if ($c_uid = (int) $db_row['uid'] && $c_status = (int) $db_row['status']) {
			// get credit
			$credit = _credit_get_credit($c_uid);

			// get billing
			$billing = _credit_get_billing($c_uid);

			// get billing for subusers if this is admin or user
			if ($c_status == 2 || $c_status == 3) {
				$billing += _credit_get_billing_parent($c_uid);
			}

			// calculate credit
			$credit = _credit_calculate_credit($credit, $billing);

			//_log("rate update uid:" . $uid . " credit:" . $credit ." billing:" . $billing . " credit:" . $credit, 3, "_credit_rate_update");

			// update user's credit field with credit
			_credit_update_credit($c_uid, $credit);
		}
	}
}

/**
 * Get user credit by uid
 * 
 * @param int $uid
 * @return float
 */
function _credit_getbyuid($uid)
{
	$credit = 0;

	if ($uid = (int) $uid && $username = user_uid2username($uid)) {
		$credit = credit_hook_rate_getusercredit($username);
	}

	return (float) $credit;
}

/**
 * Send low credit message to user inbox
 * 
 * @return void
 */
function _credit_low_notif()
{
	global $core_config;

	$db_query = "SELECT uid, parent_uid, username FROM " . _DB_PREF_ . "_tblUser WHERE flag_deleted='0'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {

		// sender's
		$uid = $db_row['uid'];
		$credit = _credit_getbyuid($uid);
		$username = $db_row['username'];

		// if credit under credit lowest limit and never been notified then notify admins, parent_uid and uid
		$credit_lowest_limit = (float) $core_config['main']['credit_lowest_limit'];
		$reg = registry_search($uid, 'feature', 'credit', 'lowest_limit_notif');
		$notified = isset($reg['feature']['credit']['lowest_limit_notif']) && $reg['feature']['credit']['lowest_limit_notif'] ? true : false;

		if ($credit && $credit_lowest_limit && $credit <= $credit_lowest_limit && !$notified) {

			// set notified
			registry_update(
				$uid,
				'feature',
				'credit',
				[
					'lowest_limit_notif' => true
				]
			);

			// notif admins
			$admins = user_getallwithstatus(2);
			foreach ( $admins as $admin ) {
				$credit_message_to_admins = sprintf(_('Username %s with account ID %d has reached lowest credit limit of %s'), $username, $uid, $credit_lowest_limit);
				recvsms_inbox_add(core_get_datetime(), _SYSTEM_SENDER_ID_, $admin['username'], $credit_message_to_admins);
			}

			// get parent
			if ($parent_uid = $db_row['parent_uid']) {
				// notif parent_uid if exists
				if ($username_parent = user_uid2username($parent_uid)) {
					$credit_message_to_parent = sprintf(_('Your subuser with username %s and account ID %d has reached lowest credit limit of %s'), $username, $uid, $credit_lowest_limit);
					recvsms_inbox_add(core_get_datetime(), _SYSTEM_SENDER_ID_, $username_parent, $credit_message_to_parent);
				}
			}

			// notif uid
			$sender_username = ($username_parent ? $username_parent : _SYSTEM_SENDER_ID_);
			$credit_message_to_self = sprintf(_('You have reached lowest credit limit of %s'), $credit_lowest_limit);
			recvsms_inbox_add(core_get_datetime(), $sender_username, $username, $credit_message_to_self);

			_log('sent notification uid:' . $uid . ' parent_uid:' . $parent_uid . ' credit_lowest_limit:' . $credit_lowest_limit, 3, "credit_low_notif");
		}


	}
}

/**
 * Process low credit notification message every 5 minutes
 * 
 * @return void
 */
function credit_hook_playsmsd()
{
	if (!core_playsmsd_timer(300)) {
		return;
	}

	_credit_low_notif();
}
