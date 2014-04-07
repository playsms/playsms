<?php
if (!auth_isvalid()) {
	auth_block();
};

// current logged in user
$c_uid = $user_config['uid'];

// BILLING
$billing = 0;
$data = billing_getdata_by_uid($c_uid);
foreach ($data AS $a) {
	$billing+= $a['count'] * $a['rate'];
}

// CREDIT
$credit = $user_config['credit'];

$tpl = array(
	'name' => 'report_user',
	'var' => array(
		'Report' => _('Report') ,
		'My report' => _('My report') ,
		'Pending' => _('Pending') ,
		'Sent' => _('Sent') ,
		'Delivered' => _('Delivered') ,
		'Failed' => _('Failed') ,
		'Deleted' => _('Deleted') ,
		'Billing' => _('Billing') ,
		'Credit' => _('Credit') ,
		'num_rows_pending' => report_count_pending($c_uid) ,
		'num_rows_sent' => report_count_sent($c_uid) ,
		'num_rows_delivered' => report_count_delivered($c_uid) ,
		'num_rows_failed' => report_count_failed($c_uid) ,
		'num_rows_deleted' => report_count_deleted($c_uid) ,
		'billing' => $billing,
		'credit' => $credit
	)
);
_p(tpl_apply($tpl));
