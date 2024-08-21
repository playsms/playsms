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

if (!auth_isadmin()) {
	auth_block();
}

// p_status values mapped to tpl array elements
$map_values = [
	'0' => 'num_rows_pending',
	'1' => 'num_rows_sent',
	'2' => 'num_rows_failed',
	'3' => 'num_rows_delivered'
];

// USER LIST RESTRIVAL
$rows = dba_search(
	_DB_PREF_ . '_tblUser',
	'username, uid, credit, status, 0 as num_rows_pending, 0 AS num_rows_sent, 0 as num_rows_delivered, 0 as num_rows_failed, 0 as num_rows_deleted',
	[
		'flag_deleted' => 0
	],
	[],
	[
		'ORDER BY' => 'status'
	]
);

$users_count = count($rows);

// populate array with the values from the mysql query
$db_query = "SELECT uid, flag_deleted, p_status, COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing GROUP BY uid, flag_deleted, p_status";
$db_result = dba_query($db_query);
while ($db_row = dba_fetch_array($db_result)) {
	// find the array key to update based on uid
	for ($i = 0; $i < $users_count; $i++) {
		if ($rows[$i]['uid'] === $db_row['uid']) {
			$array_key = $i;
			break;
		}
	}

	/*
	 * update values of pending, sent, delivered, failed and deleted messages. might be better to update the billing and credit from the last mysql query as well to avoid any incorrect values because of delays between the first and the second query on busy or overloaded systems
	 */
	if ($db_row['flag_deleted'] !== 0) {
		$rows[$array_key]['num_rows_deleted'] += (int) $db_row['count'];
	} else {
		$rows[$array_key][$map_values[$db_row['p_status']]] += (int) $db_row['count'];
	}
}

$sum_num_rows_pending = 0;
$sum_num_rows_sent = 0;
$sum_num_rows_failed = 0;
$sum_num_rows_delivered = 0;
$sum_num_rows_deleted = 0;
$sum_billing = (float) 0;
$sum_credit = (float) 0;

$loops = [];

foreach ( $rows as $row ) {
	$c_uid = $row['uid'];
	$c_status = $row['status'];
	$c_username = $row['username'];
	$c_parent = user_uid2username(user_getparentbyuid($c_uid));
	$c_credit = rate_getusercredit($c_username);

	// BILLING
	//$c_billing = 0;
	//$c_data = billing_getdata_by_uid($c_uid);
	//foreach ($c_data as $a) {
	//	$c_billing += $a['count'] * $a['rate'];
	//}

	// BILLING
	$db_query = "SELECT SUM(charge) as billing FROM " . _DB_PREF_ . "_tblBilling WHERE uid=?";
	$db_result = dba_query($db_query, [$c_uid]);
	$db_row = dba_fetch_array($db_result);
	$c_billing = isset($db_row['billing']) ? (float) $db_row['billing'] : (float) 0;


	$sum_billing += $c_billing;
	$sum_credit += $c_credit;

	$c_isadmin = $c_status == '2' ? $icon_config['admin'] : '';

	$loops[] = [
		'tr_class' => $tr_class,
		'c_username' => $c_username,
		'c_isadmin' => $c_isadmin,
		'c_parent' => $c_parent,
		'num_rows_pending' => (int) $row['num_rows_pending'],
		'num_rows_sent' => (int) $row['num_rows_sent'],
		'num_rows_failed' => (int) $row['num_rows_failed'],
		'num_rows_delivered' => (int) $row['num_rows_delivered'],
		'num_rows_deleted' => (int) $row['num_rows_deleted'],
		'c_billing' => core_display_credit($c_billing),
		'c_credit' => core_display_credit($c_credit)
	];

	// Totals
	$sum_num_rows_pending += (int) $row['num_rows_pending'];
	$sum_num_rows_sent += (int) $row['num_rows_sent'];
	$sum_num_rows_failed += (int) $row['num_rows_failed'];
	$sum_num_rows_delivered += (int) $row['num_rows_delivered'];
	$sum_num_rows_deleted += (int) $row['num_rows_deleted'];
}

$sum_total = $sum_num_rows_pending + $sum_num_rows_sent + $sum_num_rows_failed + $sum_num_rows_delivered + $sum_num_rows_deleted;

$tpl = [
	'name' => 'report_admin',
	'vars' => [
		'Report' => _('Report'),
		'All reports' => _('All reports'),
		'User' => _('User'),
		'Parent' => _('Parent'),
		'Pending' => _('Pending'),
		'Sent' => _('Sent'),
		'Delivered' => _('Delivered'),
		'Failed' => _('Failed'),
		'Deleted' => _('Deleted'),
		'Billing' => _('Billing'),
		'Credit' => _('Credit'),
		'Total' => _('Total'),
		'sum_total' => $sum_total,
		'sum_num_rows_pending' => $sum_num_rows_pending,
		'sum_num_rows_sent' => $sum_num_rows_sent,
		'sum_num_rows_failed' => $sum_num_rows_failed,
		'sum_num_rows_delivered' => $sum_num_rows_delivered,
		'sum_num_rows_deleted' => $sum_num_rows_deleted,
		'sum_billing' => core_display_credit($sum_billing),
		'sum_credit' => core_display_credit($sum_credit),
	],
	'loops' => [
		'data' => $loops,
	],
];
_p(tpl_apply($tpl));
