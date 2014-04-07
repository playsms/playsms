<?php
if (!auth_isadmin()) {
	auth_block();
};

$tpl = array(
	'name' => 'report_admin',
	'var' => array(
		'Report' => _('Report') ,
		'All reports' => _('All reports') ,
		'User' => _('User') ,
		'Pending' => _('Pending') ,
		'Sent' => _('Sent') ,
		'Delivered' => _('Delivered') ,
		'Failed' => _('Failed') ,
		'Billing' => _('Billing') ,
		'Credit' => _('Credit') ,
	)
);

$l = 0;

// USER LIST RESTRIVAL
$rows = dba_search(_DB_PREF_ . '_tblUser', 'username, uid, credit, status', '', '', array(
	'ORDER BY' => 'status'
));
foreach ($rows as $row) {
	$c_username = $row['username'];
	$c_uid = $row['uid'];
	$c_credit = $row['credit'];
	$c_status = $row['status'];
	
	// SMS PENDING
	$num_rows_pending = report_count_pending($c_uid);
	$sum_num_rows_pending = ($sum_num_rows_pending + $num_rows_pending);
	
	// SMS SENT
	$num_rows_sent = report_count_sent($c_uid);
	$sum_num_rows_sent = ($sum_num_rows_sent + $num_rows_sent);
	
	// SMS DELIVERED
	$num_rows_delivered = report_count_delivered($c_uid);
	$sum_num_rows_delivered = ($sum_num_rows_delivered + $num_rows_delivered);
	
	// SMS FAILED
	$num_rows_failed = report_count_failed($c_uid);
	$sum_num_rows_failed = ($sum_num_rows_failed + $num_rows_failed);
	
	// BILLING
	$c_billing = 0;
	$c_data = billing_getdata_by_uid($c_uid);
	foreach ($c_data AS $a) {
		$c_billing+= $a['count'] * $a['rate'];
	}
	
	$sum_billing+= $c_billing;
	$sum_credit+= $c_credit;
	
	$c_is_admin = '';
	if ($c_status == '2') {
		$c_is_admin = $icon_config['admin'];
	}
	
	$tpl['loop']['data'][] = array(
		'tr_class' => $tr_class,
		'c_username' => $c_username,
		'c_is_admin' => $c_is_admin,
		'num_rows_pending' => $num_rows_pending,
		'num_rows_sent' => $num_rows_sent,
		'num_rows_delivered' => $num_rows_delivered,
		'num_rows_failed' => $num_rows_failed,
		'c_billing' => $c_billing,
		'c_credit' => $c_credit
	);
}

$sum_total = ($sum_num_rows_pending + $sum_num_rows_delivered + $sum_num_rows_sent + $sum_num_rows_failed);

$tpl['var']['Total'] = _('Total');
$tpl['var']['sum_total'] = $sum_total;
$tpl['var']['sum_num_rows_pending'] = $sum_num_rows_pending;
$tpl['var']['sum_num_rows_sent'] = $sum_num_rows_sent;
$tpl['var']['sum_num_rows_delivered'] = $sum_num_rows_delivered;
$tpl['var']['sum_num_rows_failed'] = $sum_num_rows_failed;
$tpl['var']['sum_billing'] = $sum_billing;
$tpl['var']['sum_credit'] = $sum_credit;

_p(tpl_apply($tpl));
