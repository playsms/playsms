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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

/**
 * Get user credit balance
 * @param  integer $uid User ID
 * @return decimal      User credit balance
 */
function credit_getbalance($uid) {
	return user_getfieldbyuid($uid, 'credit');
}

/**
 * Add credit to user
 * @param  integer $uid    User ID
 * @param  decimal $amount Credit amount to add (positive value)
 * @return boolean         TRUE on success
 */
function credit_add($uid, $amount) {
	global $plugin_config;
	
	$db_table = $plugin_config['credit']['db_table'];
	$parent_uid = user_getparentbyuid($uid);
	$username = user_uid2username($uid);
	$status = user_getfieldbyuid($uid, 'status');
	$balance = credit_getbalance($uid);
	
	// add to balance
	$balance = $balance + $amount;
	
	// record it
	$id = dba_add($db_table, array(
		'parent_uid' => $parent_uid,
		'uid' => $uid,
		'username' => $username,
		'status' => $status,
		'create_datetime' => core_get_datetime() ,
		'amount' => $amount,
		'balance' => $balance,
		'flag_deleted' => 0,
	));
	
	// update user's credit
	if ($id) {
		_log('saved id:' . $id . ' parent_uid:' . $parent_uid . ' uid:' . $uid . ' username:' . $username . ' amount:' . $amount . ' balance:' . $balance, 3, 'credit_add');
		if (user_setdatabyuid($uid, array(
			'credit' => $balance
		))) {
			_log('updated uid:' . $uid . ' credit:' . $balance, 3, 'credit_add');
			return TRUE;
		} else {
			_log('fail to update uid:' . $uid . ' credit:' . $balance, 3, 'credit_add');
			dba_remove($db_table, array(
				'id' => $id
			));
			return FALSE;
		}
	} else {
		_log('fail to save parent_uid:' . $parent_uid . ' uid:' . $uid . ' username:' . $username . ' amount:' . $amount . ' balance:' . $balance, 3, 'credit_add');
		return FALSE;
	}
}

/**
 * Reduce credit from user
 * @param  integer $uid    User ID
 * @param  decimal $amount Credit amount to reduce (positive value)
 * @return boolean         TRUE on success
 */
function credit_reduce($uid, $amount) {
	
	// the amount is always negative to reduce balance
	$amount = - 1 * abs($amount);
	
	return credit_add($uid, $amount);
}

/**
 * Get HTML component select all users
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
		$option_user.= '<optgroup label="' . _('Administrators') . '">';
		
		foreach ($admins as $admin) {
			$option_user.= '<option value="' . $admin['uid'] . '">' . $admin['name'] . ' (' . $admin['username'] . ') - ' . _('Administrator') . '</option>';
		}
		$option_user.= '</optgroup>';
	}
	
	if (count($users) > 0) {
		$option_user.= '<optgroup label="' . _('Normal users') . '">';
		
		foreach ($users as $user) {
			$option_user.= '<option value="' . $user['uid'] . '">' . $user['name'] . ' (' . $user['username'] . ') - ' . _('Normal user') . '</option>';
		}
		$option_user.= '</optgroup>';
	}
	
	if (count($subusers) > 0) {
		$option_user.= '<optgroup label="' . _('Subusers') . '">';
		
		foreach ($subusers as $subuser) {
			$option_user.= '<option value="' . $subuser['uid'] . '">' . $subuser['name'] . ' (' . $subuser['username'] . ') - ' . _('Subuser') . '</option>';
		}
		$option_user.= '</optgroup>';
	}
	
	$select_user = '<select multiple name="uids[]" id="playsms-credit-select-user">' . $option_user . '</select>';
	
	return $select_user;
}

function credit_hook_webservices_output($operation, $requests) {
	global $user_config;
	
	if (!auth_isvalid()) {
		return 0;
	}
	
	$balance = 0;
	if ($operation == 'credit') {
		$balance = credit_getbalance($user_config['uid']);
	}
	
	ob_end_clean();
	header('Content-Type: text/plain');
	return $balance;
}
