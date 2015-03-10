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

$tpl = array(
	'name' => 'report_admin',
	'vars' => array(
		'Report' => _('Report'),
		'All reports' => _('All reports'),
		'User' => _('User'),
		'Pending' => _('Pending'),
		'Sent' => _('Sent'),
		'Delivered' => _('Delivered'),
		'Failed' => _('Failed'),
		'Billing' => _('Billing'),
		'Credit' => _('Credit') 
	) 
);
// p_status values mapped to tpl array elements
$map_values = array(
	'0' => 'num_rows_pending',
	'1' => 'num_rows_sent',
	'2' => 'num_rows_failed',
	'3' => 'num_rows_delivered' 
);

$l = 0;

// USER LIST RESTRIVAL
$rows = dba_search(_DB_PREF_ . '_tblUser', 'username, uid, credit, status, 0 as num_rows_pending, 0 as num_rows_sent, 0 as num_rows_delivered, 0 as num_rows_failed', array(
	'flag_deleted' => 0 
), '', array(
	'ORDER BY' => 'status' 
));

// populate array with the values from the mysql query
$db_query = "SELECT uid, flag_deleted, p_status, COUNT(*) AS count from " . _DB_PREF_ . "_tblSMSOutgoing GROUP BY uid, flag_deleted, p_status";
$db_result = dba_query($db_query);
for ($iset = array(); $irow = dba_fetch_array($db_result); $iset[] = $irow) {}

// update the rows array with values from the iset array
for ($i = 0; $i < count($iset); $i++) {
	$c = 0;
	
	// find the array key to update based on uid
	for ($ii = 0; $ii < count($rows); ++$ii) {
		if ($rows[$ii]['uid'] === $iset[$i]['uid']) {
			$array_key = $ii;
			break;
		}
	}
	
	/*
	 * update values of pending, sent, delivered, failed and deleted messages. might be better to update the billing and credit from the last mysql query as well to avoid any incorrect values because of delays between the first and the second query on busy or overloaded systems
	 */
	if ($iset[$i]['flag_deleted'] == 0) {
		$rows[$array_key][$map_values[$iset[$i]['p_status']]] += $iset[$i]['count'];
	} else {
		$rows[$array_key]['num_rows_deleted'] += $iset[$i]['count'];
	}
}

foreach ($rows as $row) {
	$c_username = $row['username'];
	$c_uid = $row['uid'];
	$c_credit = $row['credit'];
	$c_status = $row['status'];
	
	// BILLING
	$c_billing = 0;
	$c_data = billing_getdata_by_uid($c_uid);
	foreach ($c_data as $a) {
		$c_billing += $a['count'] * $a['rate'];
	}
	
	$sum_billing += $c_billing;
	$sum_credit += $c_credit;
	
	$c_isadmin = '';
	if ($c_status == '2') {
		$c_isadmin = $icon_config['admin'];
	}
	
	$tpl['loops']['data'][] = array(
		'tr_class' => $tr_class,
		'c_username' => $c_username,
		'c_isadmin' => $c_isadmin,
		'num_rows_pending' => $row['num_rows_pending'],
		'num_rows_sent' => $row['num_rows_sent'],
		'num_rows_delivered' => $row['num_rows_delivered'],
		'num_rows_failed' => $row['num_rows_failed'],
		'c_billing' => $c_billing,
		'c_credit' => $c_credit 
	);
	
	// Totals
	$sum_num_rows_pending += $row['num_rows_pending'];
	$sum_num_rows_delivered += $row['num_rows_delivered'];
	$sum_num_rows_sent += $row['num_rows_sent'];
	$sum_num_rows_failed += $row['num_rows_failed'];
}

$sum_total = ($sum_num_rows_pending + $sum_num_rows_delivered + $sum_num_rows_sent + $sum_num_rows_failed);

$tpl['vars']['Total'] = _('Total');
$tpl['vars']['sum_total'] = $sum_total;
$tpl['vars']['sum_num_rows_pending'] = $sum_num_rows_pending;
$tpl['vars']['sum_num_rows_sent'] = $sum_num_rows_sent;
$tpl['vars']['sum_num_rows_delivered'] = $sum_num_rows_delivered;
$tpl['vars']['sum_num_rows_failed'] = $sum_num_rows_failed;
$tpl['vars']['sum_billing'] = $sum_billing;
$tpl['vars']['sum_credit'] = number_format($sum_credit, 3, '.', '');

_p(tpl_apply($tpl));
