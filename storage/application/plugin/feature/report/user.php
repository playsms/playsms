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

if (!auth_isvalid()) {
	auth_block();
}

// current logged in user
$c_uid = $user_config['uid'];

// BILLING
$billing = 0;
$data = billing_getdata_by_uid($c_uid);
foreach ( $data as $a ) {
	$billing += $a['count'] * $a['rate'];
}

// CREDIT
$credit = rate_getusercredit($user_config['username']);

// p_status values mapped to tpl array elements
$map_values = array(
	'0' => 'num_rows_pending',
	'1' => 'num_rows_sent',
	'2' => 'num_rows_failed',
	'3' => 'num_rows_delivered'
);

// populate array with the values from the mysql query
$db_query = "SELECT flag_deleted, p_status, COUNT(*) AS count FROM " . _DB_PREF_ . "_tblSMSOutgoing where uid=? GROUP BY flag_deleted, p_status";
$db_result = dba_query($db_query, [$c_uid]);
$list = [];
while ($db_row = dba_fetch_array($db_result)) {
	$list[] = $db_row;
}

// define tpl before updating it with array set values
$tpl = array(
	'name' => 'report_user',
	'vars' => array(
		'Report' => _('Report'),
		'My report' => _('My report'),
		'Pending' => _('Pending'),
		'Sent' => _('Sent'),
		'Delivered' => _('Delivered'),
		'Failed' => _('Failed'),
		'Deleted' => _('Deleted'),
		'Billing' => _('Billing'),
		'Credit' => _('Credit'),
		'num_rows_pending' => 0,
		'num_rows_sent' => 0,
		'num_rows_delivered' => 0,
		'num_rows_failed' => 0,
		'num_rows_deleted' => 0,
		'billing' => core_display_credit($billing),
		'credit' => core_display_credit($credit)
	)
);

// update tpl array with values from the set array
if (isset($list) && is_array($list)) {
	for ($i = 0; $i < count($list); $i++) {
		$c = 0;
		if ($list[$i]['flag_deleted'] == 0) {
			$tpl['vars'][$map_values[$list[$i]['p_status']]] += $list[$i]['count'];
		} else {
			$tpl['vars']['num_rows_deleted'] += $list[$i]['count'];
		}
	}
}

_p(tpl_apply($tpl));